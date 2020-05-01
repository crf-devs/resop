<?php

declare(strict_types=1);

namespace App\Controller\User\Account;

use App\Domain\AvailabilitiesHelper;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="app_user_home", methods={"GET", "POST"})
 */
final class IndexController extends AbstractController
{
    private AvailabilitiesHelper $availabilitiesHelper;

    public function __construct(AvailabilitiesHelper $availabilitiesHelper)
    {
        $this->availabilitiesHelper = $availabilitiesHelper;
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $currentWeekAvailabilities = $this->availabilitiesHelper->getUserWeeklyAvailabilities($user, 'monday this week');
        $nextWeekAvailabilities = $this->availabilitiesHelper->getUserWeeklyAvailabilities($user, 'monday next week');

        return $this->render('user/index.html.twig', [
            'currentWeekAvailabilities' => $currentWeekAvailabilities,
            'nextWeekAvailabilities' => $nextWeekAvailabilities,
        ]);
    }
}
