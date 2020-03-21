<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/home", name="user_home", methods={"GET", "POST"})
     */
    public function home(Request $request): Response
    {
        return $this->render('user/home.html.twig');
    }

    /**
     * @Route("/new", name="user_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        // FIXME: Use data from session or equivalent
        $identificationNumber = '123456';
        $user->setIdentificationNumber($identificationNumber);

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Informations crées');

            return $this->redirectToRoute('user_home');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
            'identificationNumber' => $identificationNumber,
        ]);
    }

    /**
     * @Route("/edit", name="user_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request): Response
    {
        // FIXME: Use user data from session or equivalent
        $identificationNumber = '123456';
        $user = new User();
        $user->setIdentificationNumber($identificationNumber);

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Informations mises à jour');

            return $this->redirectToRoute('user_home');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'identificationNumber' => $identificationNumber,
        ]);
    }
}
