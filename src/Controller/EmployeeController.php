<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Position;
use App\Repository\TimeEntryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class EmployeeController extends AbstractController
{
    #[Route('/team', name: 'app_team')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $positionFilter = $request->query->getString('position');
        $position = Position::tryFrom($positionFilter);

        $employees = $userRepository->findEmployees($position);

        return $this->render('employee/index.html.twig', [
            'employees' => $employees,
            'positions' => Position::cases(),
            'currentPosition' => $position,
        ]);
    }

    #[Route('/team/{id}', name: 'app_team_show')]
    public function show(User $employee, TimeEntryRepository $timeEntryRepo): Response
    {
        if (!in_array('ROLE_EMPLOYEE', $employee->getRoles(), true)) {
            throw new NotFoundHttpException();
        }

        $now = new \DateTimeImmutable();
        $monthStart = $now->modify('first day of this month')->setTime(0, 0);
        $monthEntries = $timeEntryRepo->findByUserAndDateRange($employee, $monthStart, $now);

        $monthHours = array_reduce($monthEntries, fn (float $sum, $e) => $sum + ($e->getComputedHours() ?? 0), 0.0);

        // Projects worked on this month
        $projects = [];
        foreach ($monthEntries as $entry) {
            $project = $entry->getTodo()->getProject();
            if ($project) {
                $projects[$project->getId()->toRfc4122()] = $project->getName();
            }
        }

        return $this->render('employee/show.html.twig', [
            'employee' => $employee,
            'monthHours' => $monthHours,
            'activeProjects' => array_values($projects),
            'monthName' => $now->format('F Y'),
        ]);
    }
}
