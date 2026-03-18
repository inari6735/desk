<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use App\Enum\Permission;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Create admin role with all permissions
        $adminRole = new Role();
        $adminRole->setName('Admin');
        $adminRole->setPermissions(array_map(
            fn (Permission $p) => $p->value,
            Permission::cases(),
        ));
        $manager->persist($adminRole);

        // Create admin user
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->addUserRole($adminRole);
        $manager->persist($admin);

        $manager->flush();
    }
}
