<?php

namespace App\Controller\Account;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyAccountController extends AbstractController
{
    #[Route('/compte', name: 'app_account_my_account')]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function index(): Response
    {
        return $this->render('account/my_account/index.html.twig', [
            'controller_name' => 'MyAccountController',
        ]);
    }
}
