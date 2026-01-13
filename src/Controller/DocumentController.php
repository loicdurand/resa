<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DocumentController extends AbstractController
{
    #[Route('/fiche_perception', name: 'resa_document_perception')]
    public function perception(): Response
    {
        // Lorsque l'utilisateur clique sur le lien de téléchargement, on lui renvoie le document se trouvant dans /assets/pdf/perception.pdf
        $filePath = $this->getParameter('kernel.project_dir') . '/assets/pdf/perception.pdf';
        $response = new Response();
        $response->setContent(file_get_contents($filePath));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename="perception.pdf"');
        return $response;
    }

    #[Route('/fiche_reintegration', name: 'resa_document_reintegration')]
    public function reintegration(): Response
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/assets/pdf/perception.pdf';
        $response = new Response();
        $response->setContent(file_get_contents($filePath));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename="reintegration.pdf"');
        return $response;
    }

    #[Route('/fiche/{path}', name: 'resa_document_fiche')]
    public function fiche(string $path): Response
    {

        $filePath = $this->getParameter('kernel.project_dir') . '/assets/pdf/uploads/' . $path;
        // Contrôle d'erreur si le fichier n'existe pas
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Le fichier demandé n\'existe pas.');
        }

        $response = new Response();
        $response->setContent(file_get_contents($filePath));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename="reintegration.pdf"');
        return $response;
    }
}
