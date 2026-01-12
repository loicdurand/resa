<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DocumentController extends AbstractController
{
    #[Route('/fiche_perception', name: 'resa_perception')]
    public function getDocumentAction(): Response
    {
        // Lorsque l'utilisateur clique sur le lien de téléchargement, on lui renvoie le document se trouvant dans /assets/pdf/perception.pdf
        $filePath = $this->getParameter('kernel.project_dir') . '/assets/pdf/perception.pdf';
        $response = new Response();
        $response->setContent(file_get_contents($filePath));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename="perception.pdf"');
        return $response;
    }
}
