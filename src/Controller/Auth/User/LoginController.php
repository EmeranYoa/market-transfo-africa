<?php

namespace App\Controller\Auth\User;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @method getUser()
 * @method redirectToRoute(string $string)
 * @method render(string $string, array $array)
 */
class LoginController extends AbstractController
{
    #[Route('/connexion', name: 'app_auth_user_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est connecté, on le redirige vers la home page
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/user/login.html.twig', [
            'lastUsername' => $lastUsername,
            'error' => $error,
        ]);
    }
    #[Route('/logout', name: 'app_auth_user_logout')]
    public function logout()
    {
        throw new \LogicException('This method can be blank - il will be intercepted by the logout key on your firewall.');
    }
}
