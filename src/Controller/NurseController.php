<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

final class NurseController extends AbstractController
{
    // Helper method
    private function getNurseJson()
    {
        // Get nurse data from local file
        $jsonFile = $this->getParameter('kernel.project_dir') . '/src/json/nurses.json';
        if (!file_exists($jsonFile)) {
            return new JsonResponse(['error' => 'File not found'], Response::HTTP_NOT_FOUND);
        }
        $jsonData = file_get_contents($jsonFile);
        // Validate if the JSON has data
        if ($jsonData === false) {
            return new JsonResponse(['error' => 'Could not read file'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        // Decode JSON to properly interact with data
        $data = json_decode($jsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON format'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Return the json properly decoded if all checks were passed.
        return $data;
    }

    // FindByName function
    #[Route('/nurse/name/{name}', methods: ['GET'], name: 'app_find_by_name')]
    public function findByName(string $name): JsonResponse
    {
        $nurses = $this->getNurseJson();

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
        return new JsonResponse($this->getNurseJson(), Response::HTTP_OK);
    }

    #[Route('/nurse/login', name: 'hospital_login', methods: ['POST'])]
    public function nurseLogin(Request $request): JsonResponse
    {
        // Form request, gets email and password from an HTML form
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        // If the form request is null, try with JSON format
        if (!$email || !$password) {
            $requestData = json_decode($request->getContent(), true);
            $email = $requestData['email'] ?? null;
            $password = $requestData['password'] ?? null;
        }

        // Get all of the data
        $json_data = $this->getNurseJson();

        // Key that keeps track if the request matches the local file, false by default.
        $isValid = false;

        // Checks JSON data, separates all nurses into separate keys, reads and compares, 
        // if one comparison returns true, skips to JsonResponse
        if (isset($json_data['nurses']) && is_array($json_data['nurses'])) {
            foreach ($json_data['nurses'] as $nurse) {
                if ($nurse['email'] === $email && $nurse['password'] === $password) {
                    $isValid = true;
                    break;
                }
            }
        }
        return new JsonResponse(['success' => $isValid]);
    }
}
