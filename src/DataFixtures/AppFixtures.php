<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\ProjectMember;
use App\Entity\Role;
use App\Entity\Todo;
use App\Entity\User;
use App\Enum\Permission;
use App\Enum\ProjectPermission;
use App\Enum\TodoStatus;
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
        // --- Roles ---
        $adminRole = new Role();
        $adminRole->setName('Admin');
        $adminRole->setPermissions(array_map(fn (Permission $p) => $p->value, Permission::cases()));
        $manager->persist($adminRole);

        $userRole = new Role();
        $userRole->setName('User');
        $userRole->setPermissions([]);
        $manager->persist($userRole);

        // --- Users ---
        $admin = $this->createUser($manager, 'admin@example.com', 'admin', ['ROLE_ADMIN']);
        $admin->addUserRole($adminRole);

        $alice = $this->createUser($manager, 'alice@example.com', 'alice');
        $alice->addUserRole($userRole);

        $bob = $this->createUser($manager, 'bob@example.com', 'bob');
        $bob->addUserRole($userRole);

        $carol = $this->createUser($manager, 'carol@example.com', 'carol');
        $carol->addUserRole($userRole);

        // --- Projects ---
        $website = $this->createProject($manager, 'Website Redesign', 'Complete overhaul of the company website with new branding and responsive design.');
        $api = $this->createProject($manager, 'API Platform', 'Build REST API for mobile and third-party integrations.');
        $mobile = $this->createProject($manager, 'Mobile App', 'Native mobile application for iOS and Android.');

        // --- Project Members ---
        // Alice: full access to Website, view-only on API
        $this->addMember($manager, $website, $alice, [
            ProjectPermission::VIEW->value,
            ProjectPermission::CREATE_TODO->value,
            ProjectPermission::EDIT_TODO->value,
        ]);
        $this->addMember($manager, $api, $alice, [
            ProjectPermission::VIEW->value,
        ]);

        // Bob: full access to API, can create todos on Website
        $this->addMember($manager, $api, $bob, [
            ProjectPermission::VIEW->value,
            ProjectPermission::CREATE_TODO->value,
            ProjectPermission::EDIT_TODO->value,
        ]);
        $this->addMember($manager, $website, $bob, [
            ProjectPermission::VIEW->value,
            ProjectPermission::CREATE_TODO->value,
        ]);

        // Carol: view-only on Mobile, full access on API
        $this->addMember($manager, $mobile, $carol, [
            ProjectPermission::VIEW->value,
        ]);
        $this->addMember($manager, $api, $carol, [
            ProjectPermission::VIEW->value,
            ProjectPermission::CREATE_TODO->value,
            ProjectPermission::EDIT_TODO->value,
        ]);

        // --- Todos: Website Redesign ---
        $this->createTodo($manager, $website, 'Design new homepage mockup', 'Create wireframes and high-fidelity mockups for the new landing page.', TodoStatus::DONE, $alice, '-10 days');
        $this->createTodo($manager, $website, 'Implement responsive navbar', null, TodoStatus::DONE, $bob, '-5 days');
        $this->createTodo($manager, $website, 'Build hero section', 'Implement animated hero with CTA buttons.', TodoStatus::IN_PROGRESS, $alice, '+3 days');
        $this->createTodo($manager, $website, 'Footer component', null, TodoStatus::IN_PROGRESS, $bob, '+5 days');
        $this->createTodo($manager, $website, 'Contact form', 'Form with email validation and reCAPTCHA.', TodoStatus::TODO, $alice, '+10 days');
        $this->createTodo($manager, $website, 'SEO optimization', null, TodoStatus::TODO, null, '+14 days');
        $this->createTodo($manager, $website, 'Performance audit', 'Run Lighthouse and fix issues.', TodoStatus::TODO, $bob, '+20 days');

        // --- Todos: API Platform ---
        $this->createTodo($manager, $api, 'Set up API skeleton', null, TodoStatus::DONE, $bob, '-15 days');
        $this->createTodo($manager, $api, 'User authentication endpoints', 'JWT-based auth with refresh tokens.', TodoStatus::DONE, $bob, '-8 days');
        $this->createTodo($manager, $api, 'CRUD for products', null, TodoStatus::IN_PROGRESS, $carol, '+2 days');
        $this->createTodo($manager, $api, 'Rate limiting middleware', 'Implement token-bucket rate limiter.', TodoStatus::TODO, $bob, '+7 days');
        $this->createTodo($manager, $api, 'API documentation', 'Generate OpenAPI spec with Swagger UI.', TodoStatus::TODO, $alice, '+12 days');
        $this->createTodo($manager, $api, 'Integration tests', null, TodoStatus::TODO, $carol, '+15 days');

        // --- Todos: Mobile App ---
        $this->createTodo($manager, $mobile, 'Project setup and CI', 'Set up React Native project with GitHub Actions.', TodoStatus::DONE, $admin, '-20 days');
        $this->createTodo($manager, $mobile, 'Login screen', null, TodoStatus::IN_PROGRESS, $admin, '+1 day');
        $this->createTodo($manager, $mobile, 'Dashboard screen', 'Main screen showing user stats and recent activity.', TodoStatus::TODO, $carol, '+8 days');
        $this->createTodo($manager, $mobile, 'Push notifications', null, TodoStatus::TODO, null, '+18 days');

        $manager->flush();
    }

    private function createUser(ObjectManager $manager, string $email, string $password, array $roles = []): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles($roles);
        $manager->persist($user);

        return $user;
    }

    private function createProject(ObjectManager $manager, string $name, string $description): Project
    {
        $project = new Project();
        $project->setName($name);
        $project->setDescription($description);
        $manager->persist($project);

        return $project;
    }

    private function addMember(ObjectManager $manager, Project $project, User $user, array $permissions): void
    {
        $member = new ProjectMember();
        $member->setProject($project);
        $member->setUser($user);
        $member->setPermissions($permissions);
        $manager->persist($member);
    }

    private function createTodo(
        ObjectManager $manager,
        Project $project,
        string $title,
        ?string $description,
        TodoStatus $status,
        ?User $assignedTo,
        ?string $dueOffset = null,
    ): void {
        $todo = new Todo();
        $todo->setTitle($title);
        $todo->setDescription($description);
        $todo->setStatus($status);
        $todo->setProject($project);
        $todo->setAssignedTo($assignedTo);
        if ($dueOffset) {
            $todo->setDueDate(new \DateTimeImmutable($dueOffset));
        }
        $manager->persist($todo);
    }
}
