<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Nurse;
use Doctrine\ORM\EntityManagerInterface;

class NurseControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Limpia la tabla antes de cada test (opcional pero recomendable)
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('DELETE FROM nurse');
    }

    public function testCreateNurse(): void
    {
        $payload = [
            'name' => 'Test Nurse',
            'email' => 'nurse@test.com',
            'password' => '1234'
        ];

        $this->client->request(
            'POST',
            '/nurse/new',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('Nurse created', $responseData['message']);
    }

    public function testGetAllNurses(): void
    {
        // Insertar un registro de prueba
        $nurse = new Nurse();
        $nurse->setName('Ana')->setEmail('ana@test.com')->setPassword('1234');
        $this->entityManager->persist($nurse);
        $this->entityManager->flush();

        $this->client->request('GET', '/nurse/index');

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertNotEmpty($responseData);
        $this->assertEquals('Ana', $responseData[0]['name']);
    }

    public function testFindById(): void
    {
        $nurse = new Nurse();
        $nurse->setName('Laura')->setEmail('laura@test.com')->setPassword('pass');
        $this->entityManager->persist($nurse);
        $this->entityManager->flush();

        $this->client->request('GET', '/nurse/id/' . $nurse->getId());

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('nurse', $responseData);
        $this->assertEquals('Laura', $responseData['nurse']['name'] ?? '');
    }

    public function testFindByName(): void
    {
        $nurse = new Nurse();
        $nurse->setName('Sara')->setEmail('sara@test.com')->setPassword('abcd');
        $this->entityManager->persist($nurse);
        $this->entityManager->flush();

        $this->client->request('GET', '/nurse/name/Sara');

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Sara', $responseData['nurse'][0]['name']);
    }

    public function testLoginValidAndInvalid(): void
    {
        $nurse = new Nurse();
        $nurse->setName('Pablo')->setEmail('pablo@test.com')->setPassword('pass123');
        $this->entityManager->persist($nurse);
        $this->entityManager->flush();

        // Login correcto
        $this->client->request(
            'POST',
            '/nurse/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'pablo@test.com', 'password' => 'pass123'])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Login incorrecto
        $this->client->request(
            'POST',
            '/nurse/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'pablo@test.com', 'password' => 'wrong'])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testUpdateNurse(): void
    {
        $nurse = new Nurse();
        $nurse->setName('Old')->setEmail('old@test.com')->setPassword('123');
        $this->entityManager->persist($nurse);
        $this->entityManager->flush();

        $updateData = [
            'name' => 'NewName',
            'email' => 'new@test.com'
        ];

        $this->client->request(
            'PUT',
            '/nurse/id/' . $nurse->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('NewName', $responseData['nurse']['name']);
    }

    public function testDeleteNurse(): void
    {
        $nurse = new Nurse();
        $nurse->setName('DeleteMe')->setEmail('delete@test.com')->setPassword('del');
        $this->entityManager->persist($nurse);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/nurse/id/' . $nurse->getId());

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $deletedNurse = $this->entityManager->getRepository(Nurse::class)->find($nurse->getId());
        $this->assertNull($deletedNurse, 'The nurse should no longer exist in the database after deletion.');
    }
}

