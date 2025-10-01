<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Unite;
use App\Entity\Groupe;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

use App\Security\SsoServiceV2;

class SsoAuthenticator extends AbstractAuthenticator
{
    private $entityManager;
    private $urlGenerator;

    private $sso;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->sso = new SsoServiceV2();
    }

    public function supports(Request $request): ?bool
    {
        // Détermine si cet authenticator doit être utilisé (ex. : sur une route spécifique)
        if ($request->getPathInfo() == '/logout')
            return false;

        if (is_null($this->sso::user()))
            return true;

        if (!isset($_COOKIE[$_ENV['COOKIE_NAME']]))
            return true;

        if ($request->getPathInfo() == '/login')
            return true;

        return false; //isset($_COOKIE[$_ENV['COOKIE_NAME']]) && $request->getPathInfo() == '/';
    }

    public function authenticate(Request $request): Passport
    {

        if ($request->getPathInfo() === '/logout') {
            $this->sso::logout();
            return new SelfValidatingPassport(new UserBadge('', fn() => null));
        }

        $this->sso::authenticate();

        // Simule une requête au mock SSO pour récupérer les infos utilisateur
        $ssoData = $this->sso::user();

        if (!$ssoData) {
            throw new AuthenticationException('Invalid SSO token');
        }

        // Cherche ou crée l'unité dans la base
        $codeunite = $_ENV['APP_ENV'] === 'dev' ? $ssoData->codeunite : $ssoData->codeUnite;
        $unite = $this->entityManager->getRepository(Unite::class)->findOneBy(['codeunite' => $codeunite]);
        if (!$unite) {
            $unite = new Unite();
            $unite->setCodeUnite($codeunite);
            $unite->setNomCourt($ssoData->unite);
            $unite->setNomLong($ssoData->unite);
            $unite->setDepartement($ssoData->dptUnite);
            $this->entityManager->persist($unite);
            $this->entityManager->flush();
        }

        // Cherche ou crée l'utilisateur dans la base
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['nigend' => $ssoData->nigend]);

        if (!$user) {

            $user = new User();
            $user->setNigend($ssoData->nigend);
            $user->setUnite($codeunite);
            $profil = 'USR';
            if ($ssoData->specialite === 'CYBERNUM')
                $profil = 'SOLC';
            $user->setProfil($profil);
            $user->setDepartement($ssoData->dptUnite);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier(), fn() => $user));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Redirige vers une page après authentification réussie
        return new RedirectResponse($this->urlGenerator->generate('accueil'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Redirige ou affiche une erreur en cas d'échec
        return new RedirectResponse($this->urlGenerator->generate('login'));
    }
}
