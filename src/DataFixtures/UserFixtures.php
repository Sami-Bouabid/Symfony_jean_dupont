<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
   private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }
   
   
    public function load(ObjectManager $manager): void
    {
        $super_admin = $this->createSuperAdmin();

        $manager->persist($super_admin);
        $manager->flush();
    }

    private function createSuperAdmin() : User
    {
        $admin = new User();

        $password_hashed = $this->hasher->hashPassword($admin, "Azerty1234.");

        $admin->setFirstName("Jean");
        $admin->setLastName("Dupont");
        $admin->setEmail("medecine-du-monde@gmail.com");
        $admin->setPassword($password_hashed);
        $admin->setRoles(["ROLE_SUPER_ADMIN", "ROLE_ADMIN", "ROLE_USER"]);
        $admin->setIsVerified(true);
        $admin->setVerifiedAt(new DateTimeImmutable('now'));

        return $admin;
    }
}
