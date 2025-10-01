<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class NurseController extends AbstractController
{
    // FindByName function
    #[Route('/nurse/name/{name}', methods: ['GET'], name: 'app_find_by_name')]
    public function findByName(string $name): JsonResponse
    {
        // Ruta al archivo nurses.json
        $jsonFile = $this->getParameter('kernel.project_dir') . '/nurses.json';
        
        // Leer y decodificar el archivo JSON
        $jsonContent = file_get_contents($jsonFile);
        $nurses = json_decode($jsonContent, true);

        // Buscar el enfermero por nombre (Uso del " === " para que busque el nombre exacto)
        $foundNurse = null;
        foreach ($nurses as $nurse) {
            if (isset($nurse['name']) &&
                strcasecmp($nurse['name'], $name) === 0) {
                $foundNurse = $nurse;
                break;
            }
        }

        // Devolver resultado
        if ($foundNurse) {
            return $this->json([
                'nurse' => $foundNurse,
            ]);
        } else {
            return $this->json([
               'error' => 'Nurse not found',
                'message' => "Nurse {$name} not found"
            ], 404);
        }
    }
}