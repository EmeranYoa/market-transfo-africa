<?php

namespace App\Controller\Auth\User;

use App\Entity\User;
use App\Event\User\UserCreatedEvent;
use App\Form\RegisterFormType;
use App\Security\TokenGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class RegisterController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager,)
    {
    }

    /**
     * @throws \Exception
     */
    #[Route('/inscription', name: 'app_auth_user_register')]
    public function index(Request $request, UserPasswordHasherInterface $hasher,  EventDispatcherInterface $dispatcher, TokenGeneratorService $tokenGenrator): Response
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
            $user->setCreatedAt(new \DateTime());
            $user->setConfirmationToken($tokenGenrator->generate(60));
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $dispatcher->dispatch(new UserCreatedEvent($user));

            $this->addFlash(
                'success',
                'Un message avec un lien de confirmation vous a été envoyé par mail. Veuillez suivre ce lien pour activer votre compte.'
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

    #[Route('/inscription/confirmation/{id<\d+>}', name: 'app_auth_user_register_confirm')]
    public function confirmRegistration(Request $request, User $user): Response
    {
        $token = $request->get('token');
        if (empty($token) || $token !== $user->getConfirmationToken()) {
            $this->addFlash('error', "Ce token n'est pas valide");

            return $this->redirectToRoute('app_auth_user_register');
        }

        if ($user->getCreatedAt() < new \DateTime('-2 hours')) {
            $this->addFlash('error', 'Ce token a expiré');

            return $this->redirectToRoute('app_auth_user_register');
        }

        $user->setConfirmationToken(null);
        $this->entityManager->flush();
        $this->addFlash('success', 'Votre compte a été validé.');

        return $this->redirectToRoute('app_auth_user_login');
    }
}
