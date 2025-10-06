<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

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
    public function findByName(string $name): JsonResponse
    {
        $jsonData = $this->getNurseJson();

        // Find nurse by name (Use " === " to search for the exact name)
        $foundNurse = null;
        if (isset($jsonData['nurses']) && is_array($jsonData['nurses'])) {
            foreach ($jsonData['nurses'] as $nurse) {
                if ($nurse['name'] === $name) {
                    $foundNurse = $nurse;
                }
            }
        }

        // Return result with nurse data
        if ($foundNurse) {
            return $this->json([
                'nurse' => $foundNurse,
                'success' => "Nurse {$name} found!"
            ]);
        }

        return $this->json(['error' => "Nurse not found!"], 404);
    }

    // GetAll function
    #[Route('/index', methods: ['GET'], name: 'allNurses')]
    public function getAll(): JsonResponse
    {
        return new JsonResponse($this->getNurseJson(), Response::HTTP_OK);
    }

    #[Route('/login', name: 'hospital_login', methods: ['POST'])]
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

        return new JsonResponse($isValid ? ['success' => $isValid] : ['error' => 'Login credentials invalid'], $isValid ? 200 : 401);
    }

    //Login 2
    #[Route('/login', name: 'app_Nurse', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {

        //************************************************************************************************
        //RECUPERAR PARAMETROS del json
        //Indicamos ruta del json
        $jsonFilePath = $this->getParameter('kernel.project_dir') . '/data/nurses.json';
        //Para leer el contenido del json
        $jsonContent = file_get_contents($jsonFilePath);
        //Para añadir los objetos json a un array asociativo
        $usersData = json_decode($jsonContent, true);

        //************************************************************************************************        
        // POST parameter input(user, password) leer json desde postman
        // Para añadir los objetos user y password de entrada datos json a un array asociativo
        $input = json_decode($request->getContent(), true);

        $user = $input['user'];
        $password = $input['password'];
        //dd($input); Pruebas para ver en postman los datos que recoge y pare aquí      

        //************************************************************************************************
        //PARA BUSCAR Y COMPARAR        
        // Para buscar y comparar datos en la url con datos del array extraídos del json
        $loginSuccess = false; // Para que sea falso por defecto antes de buscar en el array del json
        //Comparamos y si coincide cambiamos la variable $loginSuccess a true
        foreach ($usersData as $userData) {
        
            if ($userData['user'] == $user && 
                $userData['password'] == $password) {
                
                $loginSuccess = true;
                break;
            }
        }

        //************************************************************************************************
        //DEVUELVE RESULTADO        
        // Retorna true si coinciden y false sino
        if ($loginSuccess) {
            return new JsonResponse(
                ['success' => true, 'message' => 'True'], 
                status: Response::HTTP_OK // Código 200 OK
            );
        } else {
            // Error 401 Unauthorized o 400 Bad Request
            return new JsonResponse(
                ['success' => false, 'message' => 'False'], 
                status: Response::HTTP_UNAUTHORIZED 
            ); 
        }
    
    }

}
