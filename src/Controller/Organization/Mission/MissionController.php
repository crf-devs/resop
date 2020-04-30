<?php

declare(strict_types=1);

namespace App\Controller\Organization\Mission;

use App\Entity\Mission;
use App\Entity\Organization;
use App\Form\Type\MissionType;
use App\Repository\MissionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mission")
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION')")
 */
class MissionController extends AbstractController
{
    private MissionRepository $missionRepository;

    public function __construct(MissionRepository $missionRepository)
    {
        $this->missionRepository = $missionRepository;
    }

    /**
     * @Route("/", name="app_organization_mission_index", methods={"GET"})
     */
    public function index(): Response
    {
        /** @var Organization $organization */
        $organization = $this->getUser();
        $missions = $this->missionRepository->findByOrganization($organization);

        return $this->render('organization/mission/index.html.twig', [
            'missions' => $missions,
        ]);
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
     * @Route("/{id}", name="app_organization_mission_show", methods={"GET"})
     * @Security("mission.organization == user")
     */
    public function show(Mission $mission): Response
    {
        return $this->render('organization/mission/show.html.twig', [
            'mission' => $mission,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_organization_mission_edit", methods={"GET","POST"})
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
     * @Route("/{id}/delete", name="app_organization_mission_delete", methods={"GET"})
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
