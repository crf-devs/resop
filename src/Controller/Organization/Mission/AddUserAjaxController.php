<?php

declare(strict_types=1);

namespace App\Controller\Organization\Mission;

use App\Entity\Mission;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{mission<\d+>}/users/add/{userToAdd<\d+>}", name="app_organization_mission_add_user", methods={"POST"})
 */
class AddUserAjaxController extends AbstractController
{
    public function __invoke(Mission $mission, User $userToAdd): Response
    {
        if (!$mission->users->contains($userToAdd)) {
            $mission->users->add($userToAdd);
        }

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse();
    }
}
