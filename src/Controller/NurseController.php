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
        return $this->json(['error' => "Nurse not found!"], Response::HTTP_NOT_FOUND);
    }

    // Create Nurse function
    #[Route('/new', methods: ['POST'], name: 'app_create_nurse')]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Check if email already exists
        $existing = $this->nurseRepository->findByEmail($data['email']);
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
        // Note: find() is one native method of ServiceEntityRepository and on to connect direct with the ID.
        $nurse = $this->nurseRepository->find($id);

        // Verify should the nurse exists
        if (!$nurse) {
            return $this->json(['message' => "Enfermera con ID {$id} no encontrada."], Response::HTTP_NOT_FOUND);
        }

        // Decode the JSON body of the request (JSON is expected for a PUT) 
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'Cuerpo JSON inválido o vacío'], Response::HTTP_BAD_REQUEST);
        }

        // Update only the fields provided in the JSON
        if (isset($data['name'])) {
            $nurse->setName($data['name']);
        }

        if (isset($data['email'])) {
            $nurse->setEmail($data['email']);
        }

        // WARNING: In a aplication real, the passwords should hashearse before of the save (exemple: with the Security component).
        if (isset($data['password'])) {
            $nurse->setPassword($data['password']);
        }

        // Persist the changes in the database
        // flush() It is necessary to run the updates in the DB.
        $this->entityManager->flush();

        // Retornar una respuesta de éxito con los datos actualizados
        return $this->json([
            'message' => 'Enfermera actualizada correctamente',
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
