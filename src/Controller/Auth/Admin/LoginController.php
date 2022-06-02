<?php

namespace App\Controller\Auth\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @method render(string $string, array $array)
 * @method getUser()
 * @method redirectToRoute(string $string)
 */
class LoginController extends AbstractController
{
    #[Route('/admin/connexion', name: 'app_auth_admin_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est connecté, on le redirige vers le dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('app_admin_dashboard');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/admin/login.html.twig', [
            'lastUsername' => $lastUsername,
            'error' => $error,
        ]);
    }
}
