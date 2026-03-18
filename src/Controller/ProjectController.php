<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\TaskGroup;
use App\Enum\ActionType;
use App\Enum\ProjectPermission;
use App\Repository\ProjectMemberRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskGroupRepository;
use App\Repository\TimeEntryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/projects')]
class ProjectController extends AbstractController
{
    #[Route('', name: 'app_projects')]
    public function index(
        ProjectRepository $projectRepository,
        ProjectMemberRepository $memberRepository,
    ): Response {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            $projects = $projectRepository->findBy([], ['createdAt' => 'DESC']);
        } else {
            $projects = $memberRepository->findProjectsForUser($user, ProjectPermission::VIEW->value);
        }

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/{id}', name: 'app_project_show')]
    public function show(Project $project, Request $request, ProjectMemberRepository $memberRepository, TimeEntryRepository $timeEntryRepo, TaskGroupRepository $taskGroupRepo): Response
    {
        $this->denyAccessUnlessGranted(ProjectPermission::VIEW->value, $project);

        // Current group filter
        $groupId = $request->query->getString('group');
        $currentGroup = null;
        if ($groupId) {
            $currentGroup = $taskGroupRepo->find($groupId);
        }

        // Filter todos by group
        if ($currentGroup) {
            $todos = $currentGroup->getTodos()->filter(fn ($t) => $t->getProject() === $project);
        } else {
            $todos = $project->getTodos();
        }

        // Action type breakdown
        $actionBreakdown = [];
        foreach ($project->getTodos() as $todo) {
            foreach ($todo->getTimeEntries() as $entry) {
                $type = $entry->getActionType();
                $key = $type ? $type->value : '_none';
                if (!isset($actionBreakdown[$key])) {
                    $actionBreakdown[$key] = [
                        'type' => $type,
                        'hours' => 0.0,
                    ];
                }
                $actionBreakdown[$key]['hours'] += $entry->getComputedHours() ?? 0;
            }
        }

        uasort($actionBreakdown, fn ($a, $b) => $b['hours'] <=> $a['hours']);
        $totalTrackedHours = array_sum(array_column($actionBreakdown, 'hours'));

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'todos' => $todos,
            'currentGroup' => $currentGroup,
            'canCreateTodo' => $this->isGranted(ProjectPermission::CREATE_TODO->value, $project),
            'canEditTodo' => $this->isGranted(ProjectPermission::EDIT_TODO->value, $project),
            'actionBreakdown' => $actionBreakdown,
            'totalTrackedHours' => $totalTrackedHours,
        ]);
    }
}
