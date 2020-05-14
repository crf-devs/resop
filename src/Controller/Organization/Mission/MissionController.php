<?php

declare(strict_types=1);

namespace App\Controller\Organization\Mission;

use App\Controller\Organization\AbstractOrganizationController;
use App\Domain\MissionDomain;
use App\Domain\PlanningDomain;
use App\Entity\Mission;
use App\Entity\Organization;
use App\Form\Type\MissionsSearchType;
use App\Form\Type\MissionType;
use App\Repository\MissionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION')")
 */
class MissionController extends AbstractOrganizationController
{
    private MissionRepository $missionRepository;
    private PlanningDomain $planningDomain;

    public function __construct(MissionRepository $missionRepository, PlanningDomain $planningDomain)
    {
        $this->missionRepository = $missionRepository;
        $this->planningDomain = $planningDomain;
    }

    /**
     * @Route(name="app_organization_mission_index", methods={"GET"})
     */
    public function index(): Response
    {
        $form = $this->planningDomain->generateForm(MissionsSearchType::class);
        $filters = $form->getData();

        return $this->render('organization/mission/index.html.twig', [
            'filters' => $filters,
            'form' => $form->createView(),
            'missions' => $this->missionRepository->findByFilters($filters),
        ]);
    }

    /**
     * @Route("/full", name="app_organization_mission_full_list", methods={"GET"})
     */
    public function fullList(): Response
    {
        $form = $this->planningDomain->generateForm(MissionsSearchType::class);
        $filters = $form->getData();

        return $this->render('organization/mission/list_full.html.twig', [
            'filters' => $filters,
            'form' => $form->createView(),
            'missions' => $this->missionRepository->findByFilters($filters),
        ]);
    }

    /**
     * @Route("/full/export", name="app_organization_mission_full_list_export", methods={"GET"})
     */
    public function fullListExport(MissionDomain $missionDomain): Response
    {
        $form = $this->planningDomain->generateForm(MissionsSearchType::class);
        $filters = $form->getData();
        $query = $this->missionRepository->findByFiltersQb($filters)->getQuery();
        $em = $this->getDoctrine()->getManager();

        $response = new StreamedResponse(static function () use ($query, $em, $missionDomain): void {
            $results = $query->iterate();
            $handle = fopen('php://output', 'rb+');

            if (false === $handle) {
                throw new \RuntimeException('Unable to stream the response');
            }

            fputcsv($handle, $missionDomain->getCsvHeaders());
            while (false !== ($row = $results->next())) {
                /** @var Mission $mission */
                $mission = $row[0];
                $missionRows = $missionDomain->toCsvArray($mission);
                foreach ($missionRows as $missionRow) {
                    fputcsv($handle, $missionRow);
                }
                $em->clear();
            }

            fclose($handle);
        });

        $filename = 'resop-mission.csv';
        if (!empty($filters['from']) && !empty($filters['to'])) {
            $filename = sprintf('resop-missions-%s-%s-%s.csv', $filters['from']->format('Y-m-d'), $filters['to']->format('Y-m-d'), time());
        }

        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));

        return $response;
    }

    /**
     * @Route("/new", name="app_organization_mission_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        /** @var Organization $organization */
        $organization = $this->getUser();

        $mission = new Mission();
        $mission->organization = $organization;
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($mission);
            $entityManager->flush();

            return $this->redirectToRoute('app_organization_mission_show', ['id' => $mission->id]);
        }

        return $this->render('organization/mission/new.html.twig', [
            'mission' => $mission,
            'form' => $form->createView(),
        ])->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }

    /**
     * @Route("/{id<\d+>}", name="app_organization_mission_show", methods={"GET"})
     * @Security("mission.organization == user")
     */
    public function show(Mission $mission): Response
    {
        return $this->render('organization/mission/show.html.twig', [
            'mission' => $mission,
        ]);
    }

    /**
     * @Route("/{id<\d+>}/edit", name="app_organization_mission_edit", methods={"GET","POST"})
     * @Security("mission.organization == user")
     */
    public function edit(Request $request, Mission $mission): Response
    {
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_organization_mission_show', ['id' => $mission->id]);
        }

        return $this->render('organization/mission/edit.html.twig', [
            'mission' => $mission,
            'form' => $form->createView(),
        ])->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }

    /**
     * @Route("/{id<\d+>}/delete", name="app_organization_mission_delete", methods={"GET"})
     * @Security("mission.organization == user")
     */
    public function delete(Mission $mission): RedirectResponse
    {
        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($mission);
        $entityManager->flush();

        $this->addFlash('success', 'organization.mission.deleteSuccessMessage');

        return $this->redirectToRoute('app_organization_mission_index');
    }
}
