<?php

declare(strict_types=1);

namespace App\Controller\Organization\User;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{userToShow<\d+>}/modal", name="app_organization_user_show_modal", methods={"GET"})
 */
class UserShowModalController extends AbstractController
{
    public function __invoke(User $userToShow): Response
    {
        return $this->render('organization/user/show-modal-content.html.twig', [
            'user' => $userToShow,
        ]);
    }
}
