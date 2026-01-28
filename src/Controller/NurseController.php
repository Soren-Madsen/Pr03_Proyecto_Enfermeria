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
                'password' => $nurse->getPassword()
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
    // Si la petición es OPTIONS (el navegador preguntando por permisos), respondemos OK
    if ($request->getMethod() === 'OPTIONS') {
        return new JsonResponse(null, Response::HTTP_OK);
    }

    $data = json_decode($request->getContent(), true);
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;

    if (!$email || !$password) {
        return $this->json(['success' => false, 'message' => 'Faltan datos'], 400);
    }

    $nurse = $this->nurseRepository->findOneBy(['email' => $email]);

    if ($nurse && $nurse->getPassword() === $password) {
        return $this->json([
            'success' => true,
            'id' => $nurse->getId()
        ], 200);
    }

    return $this->json(['success' => false, 'message' => 'Credenciales incorrectas'], 401);
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
                'nurse' => $foundNurse,
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
        $nurse->setName($data['name']);
        $nurse->setEmail($data['email']);
        $nurse->setPassword($data['password']);

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
        $this->entityManager->flush();
        return $this->json([
            'message' => 'Nurse update',
            'nurse' => [
                'id' => $nurse->getId(),
                'name' => $nurse->getName(),
                'email' => $nurse->getEmail(),
            ]
        ], Response::HTTP_OK);
    }

    /**
     * DeleteByID function (Elimina una enfermera por ID)
     * Método: DELETE /nurse/id/{id}
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
            'email' => $nurse->getEmail()
        ];

        $entityManager->remove($nurse);
        $entityManager->flush(); 


        return $this->json([
            'message' => "Nurse with ID {$id} successfully deleted!",
            'deleted_nurse' => $deletedNurseData
        ], Response::HTTP_OK);
    }
}
