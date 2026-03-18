<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\ProjectMember;
use App\Entity\Role;
use App\Entity\TimeEntry;
use App\Entity\Todo;
use App\Entity\User;
use App\Enum\ActionType;
use App\Enum\Permission;
use App\Enum\Position;
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

        $employeeRole = new Role();
        $employeeRole->setName('Employee');
        $employeeRole->setPermissions([Permission::USER_VIEW->value]);
        $manager->persist($employeeRole);

        // --- Users ---
        $admin = $this->createUser($manager, 'admin@example.com', 'admin', 'Admin', ['ROLE_ADMIN', 'ROLE_EMPLOYEE'], Position::CEO, '2019-01');
        $admin->addUserRole($adminRole);

        $alice = $this->createUser($manager, 'alice@example.com', 'alice', 'Alice Johnson', ['ROLE_EMPLOYEE'], Position::FRONTEND, '2022-03');
        $alice->addUserRole($employeeRole);

        $bob = $this->createUser($manager, 'bob@example.com', 'bob', 'Bob Smith', ['ROLE_EMPLOYEE'], Position::BACKEND, '2021-06');
        $bob->addUserRole($employeeRole);

        $carol = $this->createUser($manager, 'carol@example.com', 'carol', 'Carol Williams', ['ROLE_EMPLOYEE'], Position::QA, '2023-01');
        $carol->addUserRole($employeeRole);

        $dave = $this->createUser($manager, 'dave@example.com', 'dave', 'Dave Brown', ['ROLE_EMPLOYEE'], Position::DEVOPS, '2020-09');
        $dave->addUserRole($employeeRole);

        $eve = $this->createUser($manager, 'eve@example.com', 'eve', 'Eve Davis', ['ROLE_EMPLOYEE'], Position::DESIGNER, '2023-07');
        $eve->addUserRole($employeeRole);

        $frank = $this->createUser($manager, 'frank@example.com', 'frank', 'Frank Miller', ['ROLE_EMPLOYEE'], Position::PM, '2021-01');
        $frank->addUserRole($employeeRole);

        $grace = $this->createUser($manager, 'grace@example.com', 'grace', 'Grace Lee', ['ROLE_EMPLOYEE'], Position::FULLSTACK, '2024-02');
        $grace->addUserRole($employeeRole);

        $hank = $this->createUser($manager, 'hank@example.com', 'hank', 'Hank Wilson', ['ROLE_EMPLOYEE'], Position::HR, '2022-11');
        $hank->addUserRole($employeeRole);

        $ivy = $this->createUser($manager, 'ivy@example.com', 'ivy', 'Ivy Taylor', ['ROLE_EMPLOYEE'], Position::MANAGER, '2020-04');
        $ivy->addUserRole($employeeRole);

        // --- Projects ---
        $website = $this->createProject($manager, 'Website Redesign', 'Complete overhaul of the company website with new branding and responsive design.', 120);
        $api = $this->createProject($manager, 'API Platform', 'Build REST API for mobile and third-party integrations.', 200);
        $mobile = $this->createProject($manager, 'Mobile App', 'Native mobile application for iOS and Android.', 300);

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

        // --- Todos: Website Redesign (budget: 120h) ---
        $t = $this->createTodo($manager, $website, 'Design new homepage mockup', 'Create wireframes and high-fidelity mockups for the new landing page.', TodoStatus::DONE, $alice, '-10 days', 16, 18);
        $this->logTime($manager, $t, $alice, 8, '-12 days', 'Initial wireframes', '08:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $alice, 6, '-11 days', 'High-fidelity mockups', '09:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $bob, 4, '-10 days', 'Design review and revisions', '13:00', null, ActionType::MEETINGS);

        $t = $this->createTodo($manager, $website, 'Implement responsive navbar', null, TodoStatus::DONE, $bob, '-5 days', 8, 6);
        $this->logTime($manager, $t, $bob, 4, '-7 days', 'HTML/CSS structure', '09:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $bob, 2, '-6 days', 'Mobile breakpoints', '14:00', null, ActionType::TESTING);

        $t = $this->createTodo($manager, $website, 'Build hero section', 'Implement animated hero with CTA buttons.', TodoStatus::IN_PROGRESS, $alice, '+3 days', 12, 5);
        $this->logTime($manager, $t, $alice, 3, '-2 days', 'Layout and animations', '09:30', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $alice, 2, '-1 day', 'CTA buttons and responsive', '10:00', null, ActionType::DEVELOPMENT);

        $t = $this->createTodo($manager, $website, 'Footer component', null, TodoStatus::IN_PROGRESS, $bob, '+5 days', 6, 2);
        $this->logTime($manager, $t, $bob, 2, '-1 day', 'Basic structure', '08:30', null, ActionType::DEVELOPMENT);

        $this->createTodo($manager, $website, 'Contact form', 'Form with email validation and reCAPTCHA.', TodoStatus::TODO, $alice, '+10 days', 10, 0);
        $this->createTodo($manager, $website, 'SEO optimization', null, TodoStatus::TODO, null, '+14 days', 8, 0);
        $this->createTodo($manager, $website, 'Performance audit', 'Run Lighthouse and fix issues.', TodoStatus::TODO, $bob, '+20 days', 12, 0);

        // --- Todos: API Platform (budget: 200h) ---
        $t = $this->createTodo($manager, $api, 'Set up API skeleton', null, TodoStatus::DONE, $bob, '-15 days', 8, 8);
        $this->logTime($manager, $t, $bob, 5, '-17 days', 'Project setup and config', '08:00', null, ActionType::DEVOPS);
        $this->logTime($manager, $t, $bob, 3, '-16 days', 'Base controller and routing', '09:00', null, ActionType::DEVELOPMENT);

        $t = $this->createTodo($manager, $api, 'User authentication endpoints', 'JWT-based auth with refresh tokens.', TodoStatus::DONE, $bob, '-8 days', 24, 28);
        $this->logTime($manager, $t, $bob, 8, '-13 days', 'JWT setup and login', '08:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $bob, 8, '-12 days', 'Refresh tokens', '08:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $carol, 6, '-11 days', 'Registration and validation', '09:00', null, ActionType::TESTING);
        $this->logTime($manager, $t, $bob, 6, '-10 days', 'Testing and edge cases', '10:00', null, ActionType::BUG_FIXING);

        $t = $this->createTodo($manager, $api, 'CRUD for products', null, TodoStatus::IN_PROGRESS, $carol, '+2 days', 16, 10);
        $this->logTime($manager, $t, $carol, 6, '-3 days', 'Entity and endpoints', '08:30', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $carol, 4, '-2 days', 'Validation and filters', '13:00', null, ActionType::RESEARCH);

        $this->createTodo($manager, $api, 'Rate limiting middleware', 'Implement token-bucket rate limiter.', TodoStatus::TODO, $bob, '+7 days', 12, 0);
        $this->createTodo($manager, $api, 'API documentation', 'Generate OpenAPI spec with Swagger UI.', TodoStatus::TODO, $alice, '+12 days', 16, 0);
        $this->createTodo($manager, $api, 'Integration tests', null, TodoStatus::TODO, $carol, '+15 days', 20, 0);

        // --- Todos: Mobile App (budget: 300h) ---
        $t = $this->createTodo($manager, $mobile, 'Project setup and CI', 'Set up React Native project with GitHub Actions.', TodoStatus::DONE, $admin, '-20 days', 12, 10);
        $this->logTime($manager, $t, $admin, 6, '-22 days', 'React Native init and deps', '08:00', null, ActionType::DEVOPS);
        $this->logTime($manager, $t, $admin, 4, '-21 days', 'CI pipeline config', '09:00', null, ActionType::DEVOPS);

        $t = $this->createTodo($manager, $mobile, 'Login screen', null, TodoStatus::IN_PROGRESS, $admin, '+1 day', 16, 8);
        $this->logTime($manager, $t, $admin, 5, '-3 days', 'UI layout and form', '08:30', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $admin, 3, '-2 days', 'API integration', '14:00', null, ActionType::DEVELOPMENT);

        $this->createTodo($manager, $mobile, 'Dashboard screen', 'Main screen showing user stats and recent activity.', TodoStatus::TODO, $carol, '+8 days', 24, 0);
        $this->createTodo($manager, $mobile, 'Push notifications', null, TodoStatus::TODO, null, '+18 days', 20, 0);

        $manager->flush();
    }

    private function createUser(ObjectManager $manager, string $email, string $password, string $name, array $roles = [], ?Position $position = null, ?string $employedSince = null): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles($roles);
        $user->setPosition($position);
        if ($employedSince) {
            $user->setEmployedSince(new \DateTimeImmutable($employedSince . '-01'));
        }
        $manager->persist($user);

        return $user;
    }

    private function createProject(ObjectManager $manager, string $name, string $description, ?float $budgetHours = null): Project
    {
        $project = new Project();
        $project->setName($name);
        $project->setDescription($description);
        $project->setBudgetHours($budgetHours);
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
        ?float $estimatedHours = null,
        ?float $spentHours = null,
    ): Todo {
        $todo = new Todo();
        $todo->setTitle($title);
        $todo->setDescription($description);
        $todo->setStatus($status);
        $todo->setProject($project);
        $todo->setAssignedTo($assignedTo);
        $todo->setEstimatedHours($estimatedHours);
        $todo->setSpentHours($spentHours);
        if ($dueOffset) {
            $todo->setDueDate(new \DateTimeImmutable($dueOffset));
        }
        $manager->persist($todo);

        return $todo;
    }

    private function logTime(ObjectManager $manager, Todo $todo, User $user, float $hours, string $dateOffset, ?string $note = null, string $startTime = '09:00', ?string $endTime = null, ?ActionType $actionType = null): void
    {
        $entry = new TimeEntry();
        $entry->setTodo($todo);
        $entry->setUser($user);
        $entry->setHours($hours);
        $entry->setDate(new \DateTimeImmutable($dateOffset));
        $entry->setNote($note);
        $entry->setActionType($actionType);
        $entry->setStartTime(new \DateTime($startTime));
        if ($endTime) {
            $entry->setEndTime(new \DateTime($endTime));
        } else {
            $end = new \DateTime($startTime);
            $end->modify(sprintf('+%d minutes', (int) ($hours * 60)));
            $entry->setEndTime($end);
        }
        $manager->persist($entry);
    }
}
