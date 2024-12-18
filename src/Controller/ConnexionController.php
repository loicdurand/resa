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
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class ConnexionController extends AbstractController
{
  private $request;
  private $requestStack;
  private $env;
  private $session;
  private $app_const;

  public function __construct(RequestStack $requestStack)
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

    if ($this->env === 'production' && !$this->session->get('HTTP_NIGEND')) {
      return $this->redirectToRoute('index');
    }

    $users = $entityManager
      ->getRepository(User::class)
      ->findAll();

    $form = $this->createForm(UserType::class);

    $form->handleRequest($this->request);

    if ($form->isSubmitted() && $form->isValid()) {

      $data = $form->getData();
      $user = $entityManager
        ->getRepository(User::class)
        ->findOneBy(['nigend' => $data->getNigend()]);

      if (is_null($user)) {
        return $this->render('accueil/login.html.twig', array_merge($this->getAppConst(), [
          'form' => $form,
          'users' => $users,
          'result' => false
        ]));
      }

      $entityManager->flush();
      $nigend = $user->getNigend();
      $profil = $user->getProfil();
      $unite = $user->getUnite();

      // En session, on ne garde que les infos qui se trouvaient autrefois dans le Zend_Registry
      $this->session->set('HTTP_NIGEND', $nigend);
      $this->session->set('HTTP_UNITE', $unite);
      $this->session->set('HTTP_PROFIL', $profil);

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
        'app.name',
        'app.tagline',
        'app.slug',
        'app.limit_resa_months',
        'app.max_resa_duration',
        'app.minutes_select_interval',
      ] as $param
    ) {
      $AppConstName = strToUpper(str_replace('.', '_', $param));
      $this->app_const[$AppConstName] = $this->getParameter($param);
    }
  }
}
