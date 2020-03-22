<?php

declare(strict_types=1);

namespace App\Controller\User\Account;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/home", name="user_home", methods={"GET", "POST"})
 */
final class IndexController extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        return $this->render('user/index.html.twig');
    }
}
