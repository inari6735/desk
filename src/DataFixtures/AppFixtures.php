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
        // ── Roles ──
        $adminRole = new Role();
        $adminRole->setName('Admin');
        $adminRole->setPermissions(array_map(fn (Permission $p) => $p->value, Permission::cases()));
        $manager->persist($adminRole);

        $employeeRole = new Role();
        $employeeRole->setName('Employee');
        $employeeRole->setPermissions([Permission::USER_VIEW->value]);
        $manager->persist($employeeRole);

        // ── Users ──
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

        $allPerms = [ProjectPermission::VIEW->value, ProjectPermission::CREATE_TODO->value, ProjectPermission::EDIT_TODO->value];
        $viewOnly = [ProjectPermission::VIEW->value];

        // ── Projects ──
        $website = $this->createProject($manager, 'Website Redesign', 'Complete overhaul of the company website with new branding and responsive design.', 120);
        $api = $this->createProject($manager, 'API Platform', 'Build REST API for mobile and third-party integrations.', 200);
        $mobile = $this->createProject($manager, 'Mobile App', 'Native mobile application for iOS and Android.', 300);
        $crm = $this->createProject($manager, 'CRM System', 'Internal customer relationship management tool.', 400);
        $infra = $this->createProject($manager, 'Infrastructure', 'Cloud infrastructure, CI/CD, monitoring and alerting.', 150);

        // ── Project Members ──
        // Website
        $this->addMember($manager, $website, $alice, $allPerms);
        $this->addMember($manager, $website, $bob, [ProjectPermission::VIEW->value, ProjectPermission::CREATE_TODO->value]);
        $this->addMember($manager, $website, $eve, $allPerms);

        // API
        $this->addMember($manager, $api, $bob, $allPerms);
        $this->addMember($manager, $api, $carol, $allPerms);
        $this->addMember($manager, $api, $alice, $viewOnly);
        $this->addMember($manager, $api, $grace, $allPerms);

        // Mobile
        $this->addMember($manager, $mobile, $carol, $viewOnly);
        $this->addMember($manager, $mobile, $grace, $allPerms);
        $this->addMember($manager, $mobile, $alice, $allPerms);

        // CRM
        $this->addMember($manager, $crm, $bob, $allPerms);
        $this->addMember($manager, $crm, $grace, $allPerms);
        $this->addMember($manager, $crm, $carol, $allPerms);
        $this->addMember($manager, $crm, $frank, $allPerms);
        $this->addMember($manager, $crm, $alice, $viewOnly);

        // Infra
        $this->addMember($manager, $infra, $dave, $allPerms);
        $this->addMember($manager, $infra, $bob, $allPerms);

        // ════════════════════════════════════════════════════
        // WEBSITE REDESIGN
        // ════════════════════════════════════════════════════
        $t = $this->createTodo($manager, $website, 'Design new homepage mockup', 'Create wireframes and high-fidelity mockups for the new landing page.', TodoStatus::DONE, $alice, '-30 days', 16, 18);
        $this->logTime($manager, $t, $alice, 8, '-32 days', 'Initial wireframes', '08:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $alice, 6, '-31 days', 'High-fidelity mockups', '09:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $admin, 2, '-30 days', 'Stakeholder review', '10:00', null, ActionType::MEETINGS);
        $this->logTime($manager, $t, $eve, 2, '-30 days', 'Visual polish', '14:00', null, ActionType::DEVELOPMENT);

        $t = $this->createTodo($manager, $website, 'Implement responsive navbar', null, TodoStatus::DONE, $bob, '-20 days', 8, 6);
        $this->logTime($manager, $t, $bob, 4, '-22 days', 'HTML/CSS structure', '09:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $bob, 2, '-21 days', 'Mobile breakpoints', '14:00', null, ActionType::TESTING);

        $t = $this->createTodo($manager, $website, 'Build hero section', 'Implement animated hero with CTA buttons.', TodoStatus::IN_PROGRESS, $alice, '+3 days', 12, 7);
        $this->logTime($manager, $t, $alice, 3, '-5 days', 'Layout and animations', '09:30', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $alice, 2, '-4 days', 'CTA buttons and responsive', '10:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $admin, 2, '-3 days', 'Reviewed hero copy and layout', '14:00', null, ActionType::PLANNING);

        $t = $this->createTodo($manager, $website, 'Footer component', null, TodoStatus::IN_PROGRESS, $bob, '+5 days', 6, 2);
        $this->logTime($manager, $t, $bob, 2, '-2 days', 'Basic structure', '08:30', null, ActionType::DEVELOPMENT);

        $this->createTodo($manager, $website, 'Contact form', 'Form with email validation and reCAPTCHA.', TodoStatus::TODO, $alice, '+10 days', 10, 0);
        $this->createTodo($manager, $website, 'SEO optimization', null, TodoStatus::TODO, null, '+14 days', 8, 0);
        $this->createTodo($manager, $website, 'Performance audit', 'Run Lighthouse and fix issues.', TodoStatus::TODO, $bob, '+20 days', 12, 0);

        // ════════════════════════════════════════════════════
        // API PLATFORM
        // ════════════════════════════════════════════════════
        $t = $this->createTodo($manager, $api, 'Set up API skeleton', null, TodoStatus::DONE, $bob, '-40 days', 8, 8);
        $this->logTime($manager, $t, $bob, 5, '-42 days', 'Project setup and config', '08:00', null, ActionType::DEVOPS);
        $this->logTime($manager, $t, $bob, 3, '-41 days', 'Base controller and routing', '09:00', null, ActionType::DEVELOPMENT);

        $t = $this->createTodo($manager, $api, 'User authentication endpoints', 'JWT-based auth with refresh tokens.', TodoStatus::DONE, $bob, '-25 days', 24, 28);
        $this->logTime($manager, $t, $bob, 8, '-35 days', 'JWT setup and login', '08:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $bob, 8, '-34 days', 'Refresh tokens', '08:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $carol, 6, '-33 days', 'Registration and validation testing', '09:00', null, ActionType::TESTING);
        $this->logTime($manager, $t, $bob, 6, '-32 days', 'Edge case fixes', '10:00', null, ActionType::BUG_FIXING);

        $t = $this->createTodo($manager, $api, 'CRUD for products', null, TodoStatus::IN_PROGRESS, $grace, '+2 days', 16, 10);
        $this->logTime($manager, $t, $grace, 4, '-6 days', 'Entity and endpoints', '08:30', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $grace, 3, '-5 days', 'Validation logic', '09:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $carol, 3, '-4 days', 'Endpoint testing', '13:00', null, ActionType::TESTING);

        $t = $this->createTodo($manager, $api, 'Rate limiting middleware', 'Implement token-bucket rate limiter.', TodoStatus::TODO, $bob, '+7 days', 12, 0);
        $t = $this->createTodo($manager, $api, 'API documentation', 'Generate OpenAPI spec with Swagger UI.', TodoStatus::TODO, $alice, '+12 days', 16, 0);
        $t = $this->createTodo($manager, $api, 'Integration tests', null, TodoStatus::TODO, $carol, '+15 days', 20, 0);

        $t = $this->createTodo($manager, $api, 'Pagination and filtering', 'Add cursor-based pagination and filter params to all list endpoints.', TodoStatus::IN_PROGRESS, $bob, '+5 days', 10, 4);
        $this->logTime($manager, $t, $bob, 4, '-3 days', 'Cursor pagination implementation', '08:00', null, ActionType::DEVELOPMENT);

        // ════════════════════════════════════════════════════
        // MOBILE APP
        // ════════════════════════════════════════════════════
        $t = $this->createTodo($manager, $mobile, 'Project setup and CI', 'Set up React Native project with GitHub Actions.', TodoStatus::DONE, $admin, '-45 days', 12, 14);
        $this->logTime($manager, $t, $admin, 6, '-47 days', 'React Native init and deps', '08:00', null, ActionType::DEVOPS);
        $this->logTime($manager, $t, $admin, 4, '-46 days', 'CI pipeline config', '09:00', null, ActionType::DEVOPS);
        $this->logTime($manager, $t, $admin, 2, '-45 days', 'Fix build issues on CI', '10:00', null, ActionType::BUG_FIXING);
        $this->logTime($manager, $t, $dave, 2, '-45 days', 'Docker build optimization', '14:00', null, ActionType::DEVOPS);

        $t = $this->createTodo($manager, $mobile, 'Login screen', 'Implement login form with biometric auth support.', TodoStatus::DONE, $admin, '-20 days', 16, 18);
        $this->logTime($manager, $t, $admin, 5, '-25 days', 'UI layout and form', '08:30', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $admin, 3, '-24 days', 'API integration', '14:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $admin, 4, '-23 days', 'Biometric auth research', '09:00', null, ActionType::RESEARCH);
        $this->logTime($manager, $t, $admin, 3, '-22 days', 'Biometric implementation', '08:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $admin, 3, '-21 days', 'Testing on devices', '13:00', null, ActionType::TESTING);

        $t = $this->createTodo($manager, $mobile, 'Dashboard screen', 'Main screen showing user stats and recent activity.', TodoStatus::IN_PROGRESS, $grace, '+8 days', 24, 8);
        $this->logTime($manager, $t, $grace, 4, '-4 days', 'Widget layout system', '08:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $grace, 4, '-3 days', 'Stats cards and charts', '09:00', null, ActionType::DEVELOPMENT);

        $t = $this->createTodo($manager, $mobile, 'Push notifications', 'FCM integration for iOS and Android.', TodoStatus::TODO, $admin, '+18 days', 20, 0);
        $t = $this->createTodo($manager, $mobile, 'Offline mode', 'Local storage sync for offline-first usage.', TodoStatus::TODO, $grace, '+25 days', 30, 0);
        $t = $this->createTodo($manager, $mobile, 'App store submission', 'Prepare screenshots, descriptions, submit to App Store and Play Store.', TodoStatus::TODO, $admin, '+35 days', 8, 0);

        // ════════════════════════════════════════════════════
        // CRM SYSTEM
        // ════════════════════════════════════════════════════
        $t = $this->createTodo($manager, $crm, 'Database schema design', 'Design the ER diagram and initial migration for contacts, companies, deals.', TodoStatus::DONE, $bob, '-35 days', 12, 14);
        $this->logTime($manager, $t, $bob, 4, '-38 days', 'ER diagram draft', '08:00', null, ActionType::PLANNING);
        $this->logTime($manager, $t, $bob, 4, '-37 days', 'Schema review with team', '09:00', null, ActionType::MEETINGS);
        $this->logTime($manager, $t, $admin, 2, '-37 days', 'Architecture review', '14:00', null, ActionType::PLANNING);
        $this->logTime($manager, $t, $bob, 4, '-36 days', 'Migrations and seed data', '08:00', null, ActionType::DEVELOPMENT);

        $t = $this->createTodo($manager, $crm, 'Contact management CRUD', 'Full CRUD for contacts with search and pagination.', TodoStatus::DONE, $grace, '-15 days', 20, 22);
        $this->logTime($manager, $t, $grace, 6, '-20 days', 'Entity and repository', '08:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $grace, 6, '-19 days', 'Controller and forms', '08:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $grace, 4, '-18 days', 'Search and pagination', '09:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $carol, 4, '-17 days', 'QA testing all flows', '08:00', null, ActionType::TESTING);
        $this->logTime($manager, $t, $grace, 2, '-16 days', 'Bug fixes from QA', '14:00', null, ActionType::BUG_FIXING);

        $t = $this->createTodo($manager, $crm, 'Deal pipeline view', 'Kanban-style deal pipeline with drag and drop.', TodoStatus::IN_PROGRESS, $bob, '+5 days', 24, 10);
        $this->logTime($manager, $t, $bob, 4, '-7 days', 'Kanban layout structure', '08:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $bob, 3, '-6 days', 'Drag and drop JS', '09:00', null, ActionType::DEVELOPMENT);
        $this->logTime($manager, $t, $admin, 3, '-5 days', 'Planning session — pipeline stages', '10:00', null, ActionType::PLANNING);

        $t = $this->createTodo($manager, $crm, 'Email integration', 'Connect with Gmail/Outlook to log emails per contact.', TodoStatus::IN_PROGRESS, $bob, '+12 days', 30, 6);
        $this->logTime($manager, $t, $bob, 3, '-4 days', 'OAuth research for Gmail', '08:00', null, ActionType::RESEARCH);
        $this->logTime($manager, $t, $bob, 3, '-3 days', 'Gmail API integration', '09:00', null, ActionType::DEVELOPMENT);

        $this->createTodo($manager, $crm, 'Reporting dashboard', 'Charts and KPIs for sales performance.', TodoStatus::TODO, $frank, '+20 days', 20, 0);
        $this->createTodo($manager, $crm, 'Import/Export CSV', 'Bulk import and export contacts and deals.', TodoStatus::TODO, $grace, '+15 days', 12, 0);
        $this->createTodo($manager, $crm, 'Activity timeline', 'Per-contact timeline of all interactions.', TodoStatus::TODO, $bob, '+18 days', 16, 0);

        // ════════════════════════════════════════════════════
        // INFRASTRUCTURE
        // ════════════════════════════════════════════════════
        $t = $this->createTodo($manager, $infra, 'Kubernetes cluster setup', 'Provision k8s cluster on AWS EKS with Terraform.', TodoStatus::DONE, $dave, '-30 days', 20, 22);
        $this->logTime($manager, $t, $dave, 8, '-35 days', 'Terraform modules for EKS', '08:00', null, ActionType::DEVOPS);
        $this->logTime($manager, $t, $dave, 6, '-34 days', 'Networking and security groups', '08:00', null, ActionType::DEVOPS);
        $this->logTime($manager, $t, $dave, 4, '-33 days', 'Node pools and autoscaling', '09:00', null, ActionType::DEVOPS);
        $this->logTime($manager, $t, $dave, 4, '-32 days', 'Testing and documentation', '08:00', null, ActionType::DOCUMENTATION);

        $t = $this->createTodo($manager, $infra, 'CI/CD pipeline', 'GitHub Actions pipelines for all services.', TodoStatus::DONE, $dave, '-15 days', 16, 14);
        $this->logTime($manager, $t, $dave, 6, '-20 days', 'Build and test workflows', '08:00', null, ActionType::DEVOPS);
        $this->logTime($manager, $t, $dave, 4, '-19 days', 'Deploy workflows', '09:00', null, ActionType::DEVOPS);
        $this->logTime($manager, $t, $dave, 4, '-18 days', 'Environment secrets and staging', '08:00', null, ActionType::DEVOPS);

        $t = $this->createTodo($manager, $infra, 'Monitoring and alerting', 'Set up Prometheus, Grafana, PagerDuty integration.', TodoStatus::IN_PROGRESS, $dave, '+5 days', 18, 8);
        $this->logTime($manager, $t, $dave, 4, '-5 days', 'Prometheus setup', '08:00', null, ActionType::DEVOPS);
        $this->logTime($manager, $t, $dave, 4, '-4 days', 'Grafana dashboards', '09:00', null, ActionType::DEVOPS);

        $this->createTodo($manager, $infra, 'Database backup automation', 'Automated daily backups with point-in-time recovery.', TodoStatus::TODO, $dave, '+10 days', 10, 0);
        $this->createTodo($manager, $infra, 'Cost optimization', 'Review and optimize AWS spend.', TodoStatus::TODO, $admin, '+20 days', 8, 0);

        // ════════════════════════════════════════════════════
        // ADMIN CURRENT MONTH SESSION LOGS (dense)
        // This ensures the dashboard has rich data
        // ════════════════════════════════════════════════════
        $now = new \DateTimeImmutable();
        $monthStart = $now->modify('first day of this month');

        // Create a todo that admin actively works on this month across projects
        $adminTodo1 = $this->createTodo($manager, $mobile, 'Architecture review', 'Review and approve mobile architecture decisions.', TodoStatus::IN_PROGRESS, $admin, '+10 days', 20, 0);
        $adminTodo2 = $this->createTodo($manager, $crm, 'Stakeholder demos', 'Prepare and deliver demos to stakeholders.', TodoStatus::IN_PROGRESS, $admin, '+15 days', 15, 0);
        $adminTodo3 = $this->createTodo($manager, $infra, 'Cost optimization', 'Review AWS bills and optimize resource usage.', TodoStatus::IN_PROGRESS, $admin, '+20 days', 10, 0);

        // Generate ~15 working days of sessions for admin in the current month
        $spentTodo1 = 0.0;
        $spentTodo2 = 0.0;
        $spentTodo3 = 0.0;
        $day = clone $monthStart;
        $dayIndex = 0;
        while ($day <= $now) {
            $dow = (int) $day->format('N'); // 1=Mon, 7=Sun
            if ($dow <= 5) { // weekdays only
                $dateStr = $day->format('Y-m-d');

                // Morning session — varies by day of week
                if ($dayIndex % 3 === 0) {
                    $h = 3.0;
                    $this->logTime($manager, $adminTodo1, $admin, $h, $dateStr, 'Mobile arch review and feedback', '08:30', null, ActionType::PLANNING);
                    $spentTodo1 += $h;
                    $h = 2.0;
                    $this->logTime($manager, $adminTodo2, $admin, $h, $dateStr, 'Prep demo slides', '12:00', null, ActionType::DOCUMENTATION);
                    $spentTodo2 += $h;
                    $h = 1.5;
                    $this->logTime($manager, $adminTodo3, $admin, $h, $dateStr, 'AWS cost dashboard review', '14:30', null, ActionType::RESEARCH);
                    $spentTodo3 += $h;
                } elseif ($dayIndex % 3 === 1) {
                    $h = 2.0;
                    $this->logTime($manager, $adminTodo2, $admin, $h, $dateStr, 'Stakeholder demo call', '09:00', null, ActionType::MEETINGS);
                    $spentTodo2 += $h;
                    $h = 4.0;
                    $this->logTime($manager, $adminTodo1, $admin, $h, $dateStr, 'Code review and PR approvals', '11:30', null, ActionType::DEVELOPMENT);
                    $spentTodo1 += $h;
                    // Some days have no notes (to trigger dashboard warning)
                    $h = 1.0;
                    $this->logTime($manager, $adminTodo3, $admin, $h, $dateStr, null, '16:00', null, ActionType::SUPPORT);
                    $spentTodo3 += $h;
                } else {
                    $h = 3.5;
                    $this->logTime($manager, $adminTodo3, $admin, $h, $dateStr, 'Infrastructure planning and cost review', '08:00', null, ActionType::PLANNING);
                    $spentTodo3 += $h;
                    $h = 2.5;
                    $this->logTime($manager, $adminTodo1, $admin, $h, $dateStr, 'Team sync and architecture decisions', '12:00', null, ActionType::MEETINGS);
                    $spentTodo1 += $h;
                    $h = 1.5;
                    $this->logTime($manager, $adminTodo2, $admin, $h, $dateStr, 'Demo feedback and follow-ups', '15:00', null, ActionType::DOCUMENTATION);
                    $spentTodo2 += $h;
                }

                $dayIndex++;
            }
            $day = $day->modify('+1 day');
        }
        $adminTodo1->setSpentHours($spentTodo1);
        $adminTodo2->setSpentHours($spentTodo2);
        $adminTodo3->setSpentHours($spentTodo3);

        // ════════════════════════════════════════════════════
        // OTHER USERS CURRENT MONTH LOGS (for leaderboard)
        // ════════════════════════════════════════════════════
        $aliceTodo = $this->createTodo($manager, $website, 'Responsive testing', 'Cross-browser and cross-device testing.', TodoStatus::IN_PROGRESS, $alice, '+7 days', 16, 0);
        $bobTodo = $this->createTodo($manager, $api, 'Error handling', 'Standardize API error responses.', TodoStatus::IN_PROGRESS, $bob, '+8 days', 14, 0);
        $carolTodo = $this->createTodo($manager, $crm, 'QA test plan', 'Write comprehensive test plan for CRM.', TodoStatus::IN_PROGRESS, $carol, '+10 days', 20, 0);
        $daveTodo = $this->createTodo($manager, $infra, 'Log aggregation', 'Set up centralized logging with ELK stack.', TodoStatus::IN_PROGRESS, $dave, '+12 days', 18, 0);
        $graceTodo = $this->createTodo($manager, $crm, 'Company management', 'CRUD for companies with contact linking.', TodoStatus::IN_PROGRESS, $grace, '+6 days', 18, 0);

        $spentAlice = 0.0;
        $spentBob = 0.0;
        $spentCarol = 0.0;
        $spentDave = 0.0;
        $spentGrace = 0.0;

        $day = clone $monthStart;
        $dayIndex = 0;
        while ($day <= $now) {
            $dow = (int) $day->format('N');
            if ($dow <= 5) {
                $dateStr = $day->format('Y-m-d');

                // Alice — 6-7h/day
                $h = ($dayIndex % 2 === 0) ? 4.0 : 3.5;
                $this->logTime($manager, $aliceTodo, $alice, $h, $dateStr, 'Cross-browser testing session', '09:00', null, ActionType::TESTING);
                $spentAlice += $h;
                $h = ($dayIndex % 2 === 0) ? 3.0 : 3.5;
                $this->logTime($manager, $aliceTodo, $alice, $h, $dateStr, 'Fix responsive issues found', '14:00', null, ActionType::BUG_FIXING);
                $spentAlice += $h;

                // Bob — 6-8h/day
                $h = 4.0;
                $this->logTime($manager, $bobTodo, $bob, $h, $dateStr, 'Error handler implementation', '08:00', null, ActionType::DEVELOPMENT);
                $spentBob += $h;
                if ($dayIndex % 2 === 0) {
                    $h = 3.0;
                    $this->logTime($manager, $bobTodo, $bob, $h, $dateStr, 'Writing error handler tests', '13:00', null, ActionType::TESTING);
                    $spentBob += $h;
                }

                // Carol — 5-6h/day
                $h = ($dayIndex % 3 === 0) ? 3.0 : 4.0;
                $this->logTime($manager, $carolTodo, $carol, $h, $dateStr, 'Writing test cases', '09:00', null, ActionType::DOCUMENTATION);
                $spentCarol += $h;
                $h = 2.0;
                $this->logTime($manager, $carolTodo, $carol, $h, $dateStr, 'Executing test plan', '14:00', null, ActionType::TESTING);
                $spentCarol += $h;

                // Dave — 5-7h/day
                $h = 4.0;
                $this->logTime($manager, $daveTodo, $dave, $h, $dateStr, 'ELK stack configuration', '08:00', null, ActionType::DEVOPS);
                $spentDave += $h;
                if ($dayIndex % 3 !== 2) {
                    $h = 2.5;
                    $this->logTime($manager, $daveTodo, $dave, $h, $dateStr, 'Log pipeline testing', '13:30', null, ActionType::TESTING);
                    $spentDave += $h;
                }

                // Grace — 6-7h/day
                $h = 3.5;
                $this->logTime($manager, $graceTodo, $grace, $h, $dateStr, 'Company entity and forms', '08:30', null, ActionType::DEVELOPMENT);
                $spentGrace += $h;
                $h = 3.0;
                $this->logTime($manager, $graceTodo, $grace, $h, $dateStr, 'Contact-company linking', '13:00', null, ActionType::DEVELOPMENT);
                $spentGrace += $h;

                $dayIndex++;
            }
            $day = $day->modify('+1 day');
        }
        $aliceTodo->setSpentHours($spentAlice);
        $bobTodo->setSpentHours($spentBob);
        $carolTodo->setSpentHours($spentCarol);
        $daveTodo->setSpentHours($spentDave);
        $graceTodo->setSpentHours($spentGrace);

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
        // Support both relative ("-5 days") and absolute ("2026-03-05") date strings
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
