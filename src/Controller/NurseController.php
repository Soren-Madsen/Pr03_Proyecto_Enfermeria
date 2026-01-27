<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\NurseRepository;
use App\Entity\Nurse;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/nurse')]
final class NurseController extends AbstractController
{
    private NurseRepository $nurseRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(NurseRepository $nurseRepository, EntityManagerInterface $entityManager)
    {
        $this->nurseRepository = $nurseRepository;
        $this->entityManager = $entityManager;
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
        // If no nurse is found, return a 404 response (Fixed issue)
        if (!$data) {
            return $this->json(['error' => 'Nurse not found'], Response::HTTP_NOT_FOUND);
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
                'password' => $nurse->getPassword(),
                'profileImage' => $nurse->getProfileImage()
            ];
        }
        return $this->json($data, Response::HTTP_OK);
    }

    #[Route('/login', name: 'hospital_login', methods: ['POST'])]
    /*public function login(Request $request): JsonResponse
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
*/

    //Cambios del login connection con backend para retornar un ID
    #[Route('/login', name: 'hospital_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        // 1. Centralizamos la obtención de datos
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        if (!$email || !$password) {
            $data = json_decode($request->getContent(), true);
            $email = $data['email'] ?? null;
            $password = $data['password'] ?? null;
        }

        // Usamos el metodo findOneBy porque devuelve un array
        $nurse = $this->nurseRepository->findOneBy(['email' => $email]);

        // Si existe la enfermera y si la contraseña coincide retornará el ID
        if ($nurse && $nurse->getPassword() === $password) {
            return new JsonResponse([
                'success' => true,
                'id' => $nurse->getId()
            ], Response::HTTP_OK);
        }

        // Si da error retorna codigo error HTTP_UNAUTHORIZED
        return new JsonResponse([
            'success' => false,
            'message' => 'Credenciales inválidas'
        ], Response::HTTP_UNAUTHORIZED);
    }


    /**
     * FindByID function
     */
    #[Route('/id/{id}', methods: ['GET'], name: 'app_find_by_id')]
    public function findByID(string $id, NurseRepository $nurseRepository): JsonResponse
    {
        // We used the Doctrine repository to search for the Nurse entity by its ID.
        $foundNurse = $nurseRepository->find($id);
        if ($foundNurse) {
            return $this->json([
                'nurse' => [
                    'id' => $foundNurse->getId(),
                    'name' => $foundNurse->getName(),
                    'email' => $foundNurse->getEmail(),
                    'password' => $foundNurse->getPassword(),
                    'profileImage' => $foundNurse->getProfileImage()
                ],
                'success' => "Nurse {$id} found!"
            ]);
        }
        return $this->json(['error' => "Nurse not found!"], Response::HTTP_NOT_FOUND);
    }

    // Create Nurse function
    #[Route('/new', methods: ['POST'], name: 'app_create_nurse')]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Check if email already exists with Regex for validating email (Fixed issue)
        $existing = $this->nurseRepository->findByEmail($data['email']);
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json(
                ['error' => 'Invalid email format'],
                Response::HTTP_BAD_REQUEST
            );
        }
        if (!empty($existing)) {
            return $this->json(
                ['error' => 'Email already exists'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $nurse = new Nurse();
        $nurse->setName($data['name'] ?? $data['email']); //por si viene vacío
        $nurse->setEmail($data['email']);
        $nurse->setPassword($data['password']);

        // Validar y procesar imagen base64 si se proporciona
        if (!empty($data['profileImage'])) {
            $validatedImage = $this->validateAndProcessBase64Image($data['profileImage']);
            if (is_array($validatedImage) && isset($validatedImage['error'])) {
                return $this->json($validatedImage, Response::HTTP_BAD_REQUEST);
            }
            $nurse->setProfileImage($validatedImage);
        }

        $em->persist($nurse);
        $em->flush();

        return $this->json(
            ['id' => $nurse->getId(), 'message' => 'Nurse created'],
            Response::HTTP_CREATED
        );
    }

    /**
     * UpdateByID function (Update one nurse for ID)
     * Method: PUT /nurse/id/{id}
     */
    #[Route('/id/{id}', methods: ['PUT'], name: 'app_nurse_update')]
    public function updateByID(Request $request, int $id): JsonResponse
    {
        // Look for the nurse for ID
        $nurse = $this->nurseRepository->find($id);
        if (!$nurse) {
            return $this->json(['message' => "Nurse with ID {$id} not found."], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'Body JSON invalid or empty'], Response::HTTP_BAD_REQUEST);
        }
        if (isset($data['name'])) {
            $nurse->setName($data['name']);
        }
        if (isset($data['email'])) {
            $nurse->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            $nurse->setPassword($data['password']);
        }
        if (isset($data['profileImage'])) {
            $validatedImage = $this->validateAndProcessBase64Image($data['profileImage']);
            if (is_array($validatedImage) && isset($validatedImage['error'])) {
                return $this->json($validatedImage, Response::HTTP_BAD_REQUEST);
            }
            $nurse->setProfileImage($validatedImage);
        }
        $this->entityManager->flush();
        return $this->json([
            'message' => 'Nurse update',
            'nurse' => [
                'id' => $nurse->getId(),
                'name' => $nurse->getName(),
                'email' => $nurse->getEmail(),
                'profileImage' => $nurse->getProfileImage(),
            ]
        ], Response::HTTP_OK);
    }

    /**
     * DeleteByID function (Elimina una enfermera por ID)
     * Método: DELETE /nurse/id/{id}s
     */
    #[Route('/id/{id}', methods: ['DELETE'], name: 'app_delete_by_id')]
    public function deleteByID(string $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $nurse = $this->nurseRepository->find($id);

        if (!$nurse) {
            return $this->json(['error' => "Nurse with ID {$id} not found!"], Response::HTTP_NOT_FOUND);
        }

        $deletedNurseData = [
            'id' => $nurse->getId(),
            'name' => $nurse->getName(),
            'email' => $nurse->getEmail(),
            'profileImage' => $nurse->getProfileImage()
        ];

        $entityManager->remove($nurse);
        $entityManager->flush();


        return $this->json([
            'message' => "Nurse with ID {$id} successfully deleted!",
            'deleted_nurse' => $deletedNurseData
        ], Response::HTTP_OK);
    }

    /**
     * Valida y procesa imágenes en formato base64
     * Acepta: data:image/jpeg;base64,... o solo la cadena base64
     */
    private function validateAndProcessBase64Image(string $image): string|array
    {
        // Si es un data URI, extraer la parte base64
        if (strpos($image, 'data:image/') === 0) {
            if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $image, $matches)) {
                $mimeType = $matches[1];
                $base64Data = $matches[2];

                // Validar tipo MIME permitido
                $allowedMimes = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
                if (!in_array(strtolower($mimeType), $allowedMimes)) {
                    return ['error' => 'Image type not allowed. Allowed types: jpeg, jpg, png, gif, webp'];
                }
            } else {
                return ['error' => 'Invalid base64 format'];
            }
        } else {
            $base64Data = $image;
        }

        // Validar que sea una cadena base64 válida
        if (!$this->isValidBase64($base64Data)) {
            return ['error' => 'Invalid base64 encoding'];
        }

        // Decodificar para obtener el tamaño en bytes
        $decodedData = base64_decode($base64Data, true);
        if ($decodedData === false) {
            return ['error' => 'Failed to decode base64 data'];
        }

        // Limitar tamaño (ej: máximo 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if (strlen($decodedData) > $maxSize) {
            return ['error' => 'Image size exceeds maximum allowed (5MB)'];
        }

        // Retornar el data URI completo para almacenar
        if (strpos($image, 'data:image/') === 0) {
            return $image;
        } else {
            // Si solo se pasó base64, intentar detectar tipo (usar jpg por defecto)
            return 'data:image/jpeg;base64,' . $base64Data;
        }
    }

    /**
     * Valida que una cadena sea base64 válida
     */
    private function isValidBase64(string $string): bool
    {
        $decoded = base64_decode($string, true);
        if ($decoded === false) {
            return false;
        }
        // Verificar que al codificar de nuevo obtenemos la misma cadena
        return base64_encode($decoded) === $string;
    }
}
