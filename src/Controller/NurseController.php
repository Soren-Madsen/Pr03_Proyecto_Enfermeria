<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

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
            if (
                isset($nurse['name']) &&
                strcasecmp($nurse['name'], $name) === 0
            ) {
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

    // GetAll function
    #[Route('/nurse/getAll', methods: ['GET'], name: 'allNurses')]
    public function getAll(): JsonResponse
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/nurses.json';
        // Añadimos los ifs para tener el control sobre los errores que puede haber (por ejemplo, no encuentra archivo, el contenido interno del JSON no es legible...)
        if (!file_exists($filePath)) {
            return new JsonResponse(['error' => 'File not found'], Response::HTTP_NOT_FOUND);
        }
        $jsonContent = file_get_contents($filePath);
        if ($jsonContent === false) {
            return new JsonResponse(['error' => 'Could not read file'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $jsonData = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON format'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse($jsonData, Response::HTTP_OK);
    }
}
