<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\TimeEntryRepository;
use App\Repository\TodoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(TimeEntryRepository $timeEntryRepo, TodoRepository $todoRepo): Response
    {
        $stats = null;

        /** @var User|null $user */
        $user = $this->getUser();

        if ($user) {
            $now = new \DateTimeImmutable();
            $monthStart = $now->modify('first day of this month')->setTime(0, 0);
            $weekStart = $now->modify('monday this week')->setTime(0, 0);
            $todayStart = $now->setTime(0, 0);

            $monthEntries = $timeEntryRepo->findByUserAndDateRange($user, $monthStart, $now);
            $weekEntries = $timeEntryRepo->findByUserAndDateRange($user, $weekStart, $now);
            $todayEntries = $timeEntryRepo->findByUserAndDateRange($user, $todayStart, $now);

            $sumHours = fn (array $entries) => array_reduce($entries, fn (float $sum, $e) => $sum + ($e->getComputedHours() ?? 0), 0.0);

            // Count unique work days this month
            $workDays = [];
            foreach ($monthEntries as $entry) {
                $workDays[$entry->getDate()->format('Y-m-d')] = true;
            }

            // Active projects this month
            $activeProjects = [];
            foreach ($monthEntries as $entry) {
                $project = $entry->getTodo()->getProject();
                if ($project) {
                    $activeProjects[$project->getId()->toRfc4122()] = $project->getName();
                }
            }

            // Sessions missing notes
            $missingNotes = [];
            foreach ($monthEntries as $entry) {
                if ($entry->getNote() === null || trim($entry->getNote()) === '') {
                    $missingNotes[] = $entry;
                }
            }

            $monthHours = $sumHours($monthEntries);
            $daysWorked = count($workDays);

            $stats = [
                'monthHours' => $monthHours,
                'weekHours' => $sumHours($weekEntries),
                'todayHours' => $sumHours($todayEntries),
                'daysWorked' => $daysWorked,
                'avgPerDay' => $daysWorked > 0 ? $monthHours / $daysWorked : 0,
                'activeProjects' => count($activeProjects),
                'activeProjectNames' => array_values($activeProjects),
                'myTodosCount' => $todoRepo->count(['assignedTo' => $user]),
                'monthName' => $now->format('F Y'),
                'missingNotes' => $missingNotes,
                'missingNotesCount' => count($missingNotes),
            ];
        }

        return $this->render('home/index.html.twig', [
            'stats' => $stats,
        ]);
    }
}
