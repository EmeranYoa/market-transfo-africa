<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->userRepository = $userRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function supports(Request $request): ?bool
    {
        return ($request->get("_route") === "app_auth_user_login" || $request->get("_route") === "app_auth_admin_login") && $request->isMethod("POST");
    }

    public function authenticate(Request $request): Passport
    {
        $emailOrPhone = $request->get("email_or_phone");

        /** @var User */
        $user = is_numeric($emailOrPhone)
            ? $this->userRepository->findOneByPhone($emailOrPhone)
            : $this->userRepository->findOneByEmail($emailOrPhone)
        ;
        if (! $user){
            throw new CustomUserMessageAuthenticationException("Identifiant invalid!");
        }

//        dd(in_array([User::ROLE_USER, User::ROLE_SELLER], $user->getRoles()));
        if (($request->get("_route") === "app_auth_admin_login")){
            $allowAccess = false;
            foreach ($user->getRoles() as  $role){
                if (in_array($role, [User::ROLE_ADMIN, User::ROLE_SELLER])){
                    $allowAccess = true;
                    break;
                }
            }
            if (!$allowAccess){
                throw new CustomUserMessageAuthenticationException("Vous n'avez pas les droits requis pour accéder a cette page!");
            }
        }


        return new Passport(new UserBadge($emailOrPhone, function ($emailOrPhone){
            return is_numeric($emailOrPhone)
                ? $this->userRepository->findOneByPhone($emailOrPhone)
                : $this->userRepository->findOneByEmail($emailOrPhone)
            ;
        }),
            new PasswordCredentials($request->get("password")),[
                new CsrfTokenBadge("authenticate", $request->get("csrf_token")),
                new PasswordUpgradeBadge($request->get("password"), $this->userRepository),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $request->getSession()->getFlashBag()->add("success", "Bienvenue dans votre session :)");
        if ($target = $this->getTargetPath($request->getSession(), $firewallName)){
            return new RedirectResponse($target);
        }
        return $this->redirectWhenSuccessOrFailure($request);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->getFlashBag()->add("danger", $exception->getMessage());
        return $this->redirectWhenSuccessOrFailure($request, true);
    }

    /**
     * @param Request $request
     * @param bool $error
     * @return RedirectResponse
     */
    private function redirectWhenSuccessOrFailure(Request $request, bool $error = false): RedirectResponse
    {
        if ($request->get("_route") === "app_auth_user_login"){
            if ($error){
                return new RedirectResponse($this->urlGenerator->generate('app_auth_user_login'));
            }
            return new RedirectResponse($this->urlGenerator->generate('app_home'));
        }

        if ($request->get("_route") === "app_auth_admin_login"){
            if ($error){
                return new RedirectResponse($this->urlGenerator->generate('app_auth_admin_login'));
            }
            return new RedirectResponse($this->urlGenerator->generate('app_admin_dashboard'));
        }
    }
}
