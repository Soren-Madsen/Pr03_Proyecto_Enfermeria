<?php

namespace App\Repository;

use App\Entity\Nurse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Nurse>
 */
class NurseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nurse::class);
    }

    public function findByName(string $name): ?Nurse
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findAll(): array
    {
        return $this->findBy([]);
    }

    public function findByEmail(string $email): array
    {
        return $this->findBy(['email' => $email]);
    }
}
