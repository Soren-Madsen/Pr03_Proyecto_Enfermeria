<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class NurseController extends AbstractController
{
    #[Route('/enfermeros', name: 'app_listado_enfermeros')]
    public function findByName(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ListadoEnfermerosController.php',
        ]);
    }
}
