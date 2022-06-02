<?php

namespace App\Controller\Auth\User;

use App\Entity\User;
use App\Form\RegisterFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @method createForm(string $class, User $user)
 * @method redirectToRoute(string $string)
 * @method getUser()
 * @method render(string $string, array $array)
 * @method addFlash(string $string, string $string1)
 */
class RegisterController extends AbstractController
{
    #[Route('/inscription', name: 'app_auth_user_register')]
    public function index(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $entityManager): Response
    {
        // Si l'utilisateur est connecté, on le redirige vers la home
        $loggedInUser = $this->getUser();
        if ($loggedInUser) {
            return $this->redirectToRoute('app_home');
        }

        $rootErrors = [];
        $user = new User();
        $form = $this->createForm(RegisterFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $user->setPassword($hasher->hashPassword($user, $user->getPassword()));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Votre compte a été créé avec succès'
            );

            return $this->redirectToRoute("app_auth_user_login");
        }
//        else if ($form->isSubmitted()){
//            /** @var FormError $error */
//            foreach ($form->getErrors() as $error) {
//                if (null === $error->getCause()) {
//                    $rootErrors[] = $error;
//                }
//            }
//        }

        return $this->render('auth/user/register.html.twig', [
            'form' => $form->createView(),
            'errors' => $rootErrors,
        ]);
    }
}
