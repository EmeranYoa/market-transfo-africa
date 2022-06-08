<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Twig\Environment;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public const USER_LOGIN_ROUTE = "app_auth_user_login";
    public const ADMIN_LOGIN_ROUTE = "app_auth_admin_login";

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private  readonly Environment $twig
    )
    {
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $previous = $authException?->getPrevious();
        // Parque le composant security est un peu bête et ne renvoie pas un AccessDenied pour les utilisateur connecté avec un cookie
        // On redirige le traitement de cette situation vers le AccessDeniedHandler
        if ($authException instanceof InsufficientAuthenticationException &&
            $previous instanceof AccessDeniedException &&
            $authException->getToken() instanceof RememberMeToken
        ) {
            return new Response($this->twig->render('bundles/TwigBundle/Exception/error403.html.twig'), Response::HTTP_FORBIDDEN);
        }

        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            return new JsonResponse(
                ['title' => "Vous n'avez pas les permissions suffisantes pour effectuer cette action"],
                Response::HTTP_FORBIDDEN
            );
        }

        return new RedirectResponse($this->getLoginUrl($request));
    }

    protected function getLoginUrl(Request $request): string
    {
        if($request->get("_route") === "app_admin_dashboard"){
            return $this->urlGenerator->generate(self::ADMIN_LOGIN_ROUTE);
        }
        return $this->urlGenerator->generate(self::USER_LOGIN_ROUTE);
    }
}