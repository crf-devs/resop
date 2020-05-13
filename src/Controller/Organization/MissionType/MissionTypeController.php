<?php

declare(strict_types=1);

namespace App\Controller\Organization\MissionType;

use App\Controller\Organization\AbstractOrganizationController;
use App\Entity\MissionType;
use App\Entity\Organization;
use App\Form\Type\MissionTypeType;
use App\Repository\MissionTypeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION')")
 */
class MissionTypeController extends AbstractOrganizationController
{
    private MissionTypeRepository $missionTypeRepository;

    public function __construct(MissionTypeRepository $missionTypeRepository)
    {
        $this->missionTypeRepository = $missionTypeRepository;
    }

    /**
     * @Route(name="app_organization_mission_type_index", methods={"GET"})
     */
    public function index(): Response
    {
        /** @var Organization $organization */
        $organization = $this->getUser();
        $missionTypes = $this->missionTypeRepository->findByOrganization($organization);

        return $this->render('organization/mission_type/index.html.twig', [
            'mission_types' => $missionTypes,
        ]);
    }

    /**
     * @Route("/new", name="app_organization_mission_type_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        /** @var Organization $organization */
        $organization = $this->getUser();

        $missionType = new MissionType();
        $missionType->organization = $organization;
        $form = $this->createForm(MissionTypeType::class, $missionType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($missionType);
            $entityManager->flush();

            return $this->redirectToRoute('app_organization_mission_type_index');
        }

        return $this->render('organization/mission_type/new.html.twig', [
            'mission_type' => $missionType,
            'form' => $form->createView(),
        ])->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }

    /**
     * @Route("/{id}/edit", name="app_organization_mission_type_edit", methods={"GET","POST"})
     * @Security("missionType.organization == user")
     */
    public function edit(Request $request, MissionType $missionType): Response
    {
        $form = $this->createForm(MissionTypeType::class, $missionType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_organization_mission_type_index');
        }

        return $this->render('organization/mission_type/edit.html.twig', [
            'mission_type' => $missionType,
            'form' => $form->createView(),
        ])->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }
}
