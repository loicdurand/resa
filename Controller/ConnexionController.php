<?php
// src/Controller/IndexController.php
namespace App\Controller;

use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\User;
use App\Entity\Atelier;
use App\Entity\Role;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Service\LdapService;

class ConnexionController extends AbstractController
{
  private $request;
  private $requestStack;
  private $env;
  private $session;
  private $app_const;

  public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
  {
    $this->request = Request::createFromGlobals();
    $this->requestStack = $requestStack;
    $this->session = $this->requestStack->getSession();
  }

  #[Route('/logout', name: 'logout')]
  public function logout()
  {
    $this->session->clear();
    return $this->redirectToRoute('login');
  }

  #[Route('/connexion', name: 'login')]
  public function login(EntityManagerInterface $entityManager)
  {
    $this->setAppConst();

    $this->env = $this->getParameter('app.env');

    // if ($this->env === 'production' && !$this->session->get('HTTP_NIGEND')) {
    //   return $this->redirectToRoute('index');
    // }

    $users = [];

    if ($this->env !== 'production') {
      $users = $entityManager
        ->getRepository(User::class)
        ->findAll();
    }

    $form = $this->createForm(UserType::class);

    $form->handleRequest($this->request);

    if ($form->isSubmitted() && $form->isValid()) {

      $data = $form->getData();
      $nigend = $data->getNigend();

      $ldap = new LdapService(); 
      $ldap_user = $ldap->get_user_from_ldap($nigend);

      if (is_null($ldap_user) && $this->app_const['APP_MACHINE'] !== 'chrome') {
        return $this->render('accueil/login.html.twig', array_merge($this->getAppConst(), [
          'form' => $form,
          'users' => $users,
          'result' => false
        ]));
      }

      $user = $entityManager
        ->getRepository(User::class)
        ->findOneBy(['nigend' => $nigend]); 

      if (is_null($user)) {
        $profil = $entityManager
          ->getRepository(Role::class)
          ->findOneBy(['nom' => $ldap_user->profil]);

        $entity = new User();
        $entity->setNigend($nigend);
        $entity->setUnite($ldap_user->unite_id);
        $entity->setProfil($ldap_user->profil);
        $entity->setDepartement($ldap_user->departement);
        $entityManager->persist($entity);
        $entityManager->flush();
        $user = $entity;
      }

      if ($this->app_const['APP_MACHINE'] === 'chrome') {
        $ldap_user = new \stdClass();
        $ldap_user->nigend = $nigend;
        $ldap_user->unite = $user->getUnite();
        $ldap_user->profil = $user->getProfil();
        $ldap_user->departement = $user->getDepartement();
      }

      $nigend = $user->getNigend();
      $profil = $user->getProfil();
      $unite = $user->getUnite();
      $dept = $user->getDepartement();

      if($profil === 'CSAG'){
        $atelier = $entityManager
          ->getRepository(Atelier::class)
          ->findOneBy(['code_unite' => $unite]);
        
        if(is_null($atelier)){
          $ldap_unite = $ldap->get_unite_from_ldap($unite);
          $unite = $ldap->format_ldap_unite($ldap_unite);

          $entity = new Atelier();
          $entity->setCodeUnite($unite->code);
          $entity->setNomCourt($unite->nom_court);
          $entity->setNomLong($unite->nom);
          $entity->setDepartement($dept);
          $entityManager->persist($entity);
          $entityManager->flush();
          $code_unite = $unite->code;
        }
      }

      // En session, on ne garde que les infos qui se trouvaient autrefois dans le Zend_Registry
      $this->session->set('HTTP_NIGEND', $nigend);
      $this->session->set('HTTP_UNITE', $unite);
      $this->session->set('HTTP_PROFIL', $profil);
      $this->session->set('HTTP_DEPARTEMENT', $dept);

      return $this->redirectToRoute('accueil');
    }

    return $this->render('accueil/login.html.twig', array_merge(
      $this->getAppConst(),
      [
        'form' => $form,
        'users' => $users,
        'result' => 'none'
      ]
    ));
  }

  private function addZeros($str, $maxlen = 2)
  {
    $str = '' . $str;
    while (strlen($str) < $maxlen)
      $str = "0" . $str;
    return $str;
  }

  private function getAppConst()
  {
    return $this->app_const;
  }

  private function setAppConst()
  {
    $this->app_const = [];
    //dd($this->getParameter('app.max_resa_duration'));
    foreach (
      [
        'app.env',
        'app.machine',
        'app.name',
        'app.tagline',
        'app.slug',
        'app.limit_resa_months',
        'app.max_resa_duration',
        'app.minutes_select_interval',
        'app.token_gives_full_access'
      ] as $param
    ) {
      $AppConstName = strToUpper(str_replace('.', '_', $param));
      $this->app_const[$AppConstName] = $this->getParameter($param);
    }
  }
}
