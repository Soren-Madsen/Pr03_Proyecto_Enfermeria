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
    private NurseRepository $nurseRepository;

    public function __construct(NurseRepository $nurseRepository)
    {
        $this->nurseRepository = $nurseRepository;
    }

    // FindByName function
    #[Route('/name/{name}', methods: ['GET'], name: 'app_find_by_name')]
    public function findByName(string $name): JsonResponse
    {
        $nurse = $this->nurseRepository->findByName($name);

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
        $nurses = $this->nurseRepository->findAll();
        $data = [];
        foreach ($nurses as $nurse) {
            $data[] = [
                'id' => $nurse->getId(),
                'name' => $nurse->getName(),
                'email' => $nurse->getEmail(),
                'password' => $nurse->getPassword()
            ];
        }
        return $this->json($data, Response::HTTP_OK);
    }

    #[Route('/login', name: 'hospital_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        // Form request, gets email and password from an HTML form
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        // If the form request is null, try with JSON headers
        if (!$email || !$password) {
            $requestData = json_decode($request->getContent(), true);
            $email = $requestData['email'] ?? null;
            $password = $requestData['password'] ?? null;
        }

        // Get all of the data
        $nurses = $this->nurseRepository->findByEmail($email);

        // Key that keeps track if the request matches the local file, false by default.
        $isValid = false;

        // Checks DB data, separates all nurses into separate keys, reads and compares, 
        // if one comparison returns true, skips to JsonResponse
        if (isset($nurses) && is_array($nurses)) {
            foreach ($nurses as $nurse) {
                if ($nurse->getEmail() === $email && $nurse->getPassword() === $password) {
                    $isValid = true;
                    break;
                }
            }
        }
        return new JsonResponse(['success' => $isValid], $isValid ? Response::HTTP_OK : Response::HTTP_UNAUTHORIZED);
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

    /**
     * UpdateByID function (Actualiza una enfermera por ID)
     * Método: PUT /nurse/id/{id}
     */
    #[Route('/id/{id}', methods: ['PUT'], name: 'app_update_by_id')]
    public function updateByID(string $id, Request $request): JsonResponse
    {
        // 1. Buscar la entidad Nurse por su ID.
        $nurse = $this->nurseRepository->find($id);

        if (!$nurse) {
            return $this->json(['error' => "Nurse with ID {$id} not found!"], Response::HTTP_NOT_FOUND);
        }

        // 2. Decodificar el cuerpo JSON de la petición.
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return $this->json(['error' => 'Invalid or empty JSON body provided.'], Response::HTTP_BAD_REQUEST);
        }

        // 3. Aplicar los cambios a la entidad (Actualización manual).
        if (isset($data['name'])) {
            $nurse->setName($data['name']);
        }
        if (isset($data['email'])) {
            $nurse->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            // NOTA: En una aplicación real, se debe hashear la contraseña.
            $nurse->setPassword($data['password']);
        }

        // 4. Persistir los cambios. Asume que NurseRepository tiene un método save() 
        // que llama a $entityManager->flush().
        $this->nurseRepository->save($nurse);

        // 5. Devolver una respuesta exitosa, usando el formato de datos de GetAll.
        return $this->json([
            'id' => $nurse->getId(),
            'name' => $nurse->getName(),
            'email' => $nurse->getEmail(),
            'message' => "Nurse with ID {$id} successfully updated!"
        ], Response::HTTP_OK);
    }

}
