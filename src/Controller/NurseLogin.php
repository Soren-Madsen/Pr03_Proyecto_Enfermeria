<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class NurseLogin extends AbstractController
{
    private function getNurseCredentials() {
        // Get nurse credentials from local file
        $jsonFile = $this->getParameter('kernel.project_dir') . '/src/json/nurses.json';
        $jsonData = file_get_contents($jsonFile);
        // Decode JSON to properly interact with data
        $data = json_decode($jsonData, true);
        
        return $data;
    }
    
    #[Route('/nurse/login', name: 'hospital_login', methods: ['POST'])]
    public function nurseLogin(Request $request): JsonResponse {
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
        $json_data = $this->getNurseCredentials();
        
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
