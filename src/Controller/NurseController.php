<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\NurseRepository;

#[Route('/nurse')]
final class NurseController extends AbstractController
{
    // Helper method
    private function getNurseJson()
    {
        // Get nurse data from local file
        $jsonFile = $this->getParameter('kernel.project_dir') . '/src/json/nurses.json';
        if (!file_exists($jsonFile)) {
            return ['error' => 'File not found'];
        }
        $jsonData = @file_get_contents($jsonFile);
        // Validate if the JSON has data
        if ($jsonData === false) {
            return ['error' => 'Could not read file'];
        }
        // Decode JSON to properly interact with data
        $data = json_decode($jsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON format'];
        }

        // Return the json properly decoded if all checks were passed.
        return $data;
    }

    // FindByName function
    #[Route('/name/{name}', methods: ['GET'], name: 'app_find_by_name')]
    public function findByName(string $name, NurseRepository $nurseRepository): JsonResponse
    {
        $nurse = $nurseRepository->findByName($name);

        $data = [];

        if ($nurse) {
            $data[] = [
                'name' => $nurse->getName(),
                'email' => $nurse->getEmail(),
                'password' => $nurse->getPassword(),
            ];
        }

        return $this->json(['nurse' => $data], Response::HTTP_OK);
    }

    // GetAll function
    #[Route('/index', methods: ['GET'], name: 'allNurses')]
    public function getAll(): JsonResponse
    {
        return new JsonResponse($this->getNurseJson(), Response::HTTP_OK);
    }

    #[Route('/login', name: 'hospital_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
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

        if (isset($json_data['error'])) {
            return new JsonResponse($json_data, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Key that keeps track if the request matches the local file, false by default.
        $isValid = false;

        // Checks JSON data, separates all nurses into separate keys, reads and compares, 
        // if one comparison returns false, skips to JsonResponse
        if (isset($json_data['nurses']) && is_array($json_data['nurses'])) {
            foreach ($json_data['nurses'] as $nurse) {
                if ($nurse['email'] === $email && $nurse['password'] === $password) {
                    $isValid = true;
                    break;
                }
            }
        }

        return new JsonResponse($isValid ? [
            'success' => $isValid
        ] : [
            'error' => $isValid
        ], $isValid ? Response::HTTP_OK : Response::HTTP_UNAUTHORIZED);
    }

    /**
     * FindByID function
     */
    #[Route('/id/{id}', methods: ['GET'], name: 'app_find_by_id')]
    public function findByID(string $id, NurseRepository $nurseRepository): JsonResponse
    {
        // Usamos el repositorio de Doctrine para buscar la entidad Nurse por su ID.
        $foundNurse = $nurseRepository->find($id);
        if ($foundNurse) {
            return $this->json([
                'nurse' => $foundNurse,
                'success' => "Nurse {$id} found!"
            ]);
        }
        return $this->json(['error' => "Nurse not found!"], JsonResponse::HTTP_NOT_FOUND);
    }
}
