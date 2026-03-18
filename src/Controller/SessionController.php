<?php

namespace App\Controller;

use App\Entity\TimeEntry;
use App\Form\SessionEntryFormType;
use App\Repository\TimeEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/sessions')]
class SessionController extends AbstractController
{
    #[Route('', name: 'app_sessions')]
    public function index(Request $request, TimeEntryRepository $repo): Response
    {
        $now = new \DateTimeImmutable();
        $from = $request->query->getString('from')
            ? new \DateTimeImmutable($request->query->getString('from'))
            : $now->modify('monday this week');
        $to = $request->query->getString('to')
            ? new \DateTimeImmutable($request->query->getString('to'))
            : $now;

        // Clamp 'to' to today
        if ($to > $now) {
            $to = $now;
        }

        $entries = $repo->findByUserAndDateRange($this->getUser(), $from, $to);

        // Group by date
        $days = [];
        foreach ($entries as $entry) {
            $dateKey = $entry->getDate()->format('Y-m-d');
            $days[$dateKey][] = $entry;
        }

        // Fill empty days in range
        $period = new \DatePeriod($from, new \DateInterval('P1D'), $to->modify('+1 day'));
        $allDays = [];
        foreach ($period as $day) {
            $key = $day->format('Y-m-d');
            $allDays[$key] = $days[$key] ?? [];
        }
        krsort($allDays);

        // Compute daily totals
        $dailyTotals = [];
        foreach ($allDays as $dateKey => $dayEntries) {
            $total = 0.0;
            foreach ($dayEntries as $entry) {
                $total += $entry->getComputedHours() ?? 0;
            }
            $dailyTotals[$dateKey] = $total;
        }

        return $this->render('session/index.html.twig', [
            'days' => $allDays,
            'dailyTotals' => $dailyTotals,
            'from' => $from,
            'to' => $to,
        ]);
    }

    #[Route('/new', name: 'app_session_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $entry = new TimeEntry();
        $entry->setUser($this->getUser());

        // Pre-fill date from query param
        $dateParam = $request->query->getString('date');
        if ($dateParam) {
            $entry->setDate(new \DateTimeImmutable($dateParam));
        }

        $form = $this->createForm(SessionEntryFormType::class, $entry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Compute hours from start/end
            $entry->setHours($entry->getComputedHours());

            $em->persist($entry);

            // Update cached spentHours on todo
            $todo = $entry->getTodo();
            $todo->setSpentHours($todo->getTotalLoggedHours() + $entry->getHours());

            $em->flush();

            $this->addFlash('success', 'Session logged.');

            return $this->redirectToRoute('app_sessions', [
                'from' => $entry->getDate()->format('Y-m-d'),
                'to' => $entry->getDate()->format('Y-m-d'),
            ]);
        }

        return $this->render('session/form.html.twig', [
            'form' => $form,
            'entry' => null,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_session_edit')]
    public function edit(TimeEntry $entry, Request $request, EntityManagerInterface $em): Response
    {
        // Only own entries
        if ($entry->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $oldHours = $entry->getComputedHours();

        $form = $this->createForm(SessionEntryFormType::class, $entry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entry->setHours($entry->getComputedHours());

            // Update cached spentHours on todo
            $todo = $entry->getTodo();
            $diff = $entry->getHours() - $oldHours;
            $todo->setSpentHours(($todo->getSpentHours() ?? 0) + $diff);

            $em->flush();

            $this->addFlash('success', 'Session updated.');

            return $this->redirectToRoute('app_sessions', [
                'from' => $entry->getDate()->format('Y-m-d'),
                'to' => $entry->getDate()->format('Y-m-d'),
            ]);
        }

        return $this->render('session/form.html.twig', [
            'form' => $form,
            'entry' => $entry,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_session_delete', methods: ['POST'])]
    public function delete(TimeEntry $entry, Request $request, EntityManagerInterface $em): Response
    {
        if ($entry->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $date = $entry->getDate();

        if ($this->isCsrfTokenValid('delete-session-'.$entry->getId(), $request->getPayload()->getString('_token'))) {
            // Update cached spentHours on todo
            $todo = $entry->getTodo();
            $todo->setSpentHours(max(0, ($todo->getSpentHours() ?? 0) - ($entry->getComputedHours() ?? 0)));

            $em->remove($entry);
            $em->flush();

            $this->addFlash('success', 'Session deleted.');
        }

        return $this->redirectToRoute('app_sessions', [
            'from' => $date->format('Y-m-d'),
            'to' => $date->format('Y-m-d'),
        ]);
    }
}
