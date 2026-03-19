<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\TimeEntry;
use App\Entity\Todo;
use App\Enum\ProjectPermission;
use App\Enum\TodoStatus;
use App\Form\TimeEntryFormType;
use App\Form\TodoFormType;
use App\Repository\ProjectRepository;
use App\Repository\TodoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_USER')]
class TodoController extends AbstractController
{
    #[Route('/todos', name: 'app_todos')]
    public function index(Request $request, TodoRepository $todoRepository, ProjectRepository $projectRepository): Response
    {
        $search = $request->query->getString('q');
        $statusFilter = TodoStatus::tryFrom($request->query->getString('status'));
        $projectIdStr = $request->query->getString('project');
        $projectId = $projectIdStr ? Uuid::fromString($projectIdStr) : null;
        $sort = $request->query->getString('sort', 'createdAt');
        $direction = $request->query->getString('dir', 'DESC');
        $page = max(1, $request->query->getInt('page', 1));

        $result = $todoRepository->paginateUserTodos(
            $this->getUser(),
            $page,
            15,
            $search ?: null,
            $statusFilter,
            $projectId,
            $sort,
            $direction,
        );

        $editableTodos = [];
        foreach ($result['items'] as $todo) {
            if ($todo->getProject() && $this->isGranted(ProjectPermission::EDIT_TODO->value, $todo->getProject())) {
                $editableTodos[$todo->getId()->toRfc4122()] = true;
            }
        }

        // Projects for filter dropdown
        $userProjects = $projectRepository->createQueryBuilder('p')
            ->where('p.id IN (SELECT IDENTITY(t2.project) FROM App\Entity\Todo t2 WHERE t2.assignedTo = :user)')
            ->setParameter('user', $this->getUser())
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('todo/index.html.twig', [
            'todos' => $result['items'],
            'total' => $result['total'],
            'pages' => $result['pages'],
            'page' => $page,
            'editableTodos' => $editableTodos,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'projectId' => $projectIdStr,
            'sort' => $sort,
            'direction' => $direction,
            'projects' => $userProjects,
            'statuses' => TodoStatus::cases(),
        ]);
    }

    #[Route('/todos/{id}', name: 'app_todo_show', priority: -1)]
    public function show(Todo $todo, Request $request, EntityManagerInterface $em): Response
    {
        $project = $todo->getProject();
        $this->denyAccessUnlessGranted(ProjectPermission::VIEW->value, $project);

        $canEdit = $this->isGranted(ProjectPermission::EDIT_TODO->value, $project);

        $timeEntryForm = null;
        if ($canEdit) {
            $timeEntry = new TimeEntry();
            $timeEntry->setTodo($todo);
            $timeEntry->setUser($this->getUser());

            $timeEntryForm = $this->createForm(TimeEntryFormType::class, $timeEntry);
            $timeEntryForm->handleRequest($request);

            if ($timeEntryForm->isSubmitted() && $timeEntryForm->isValid()) {
                $em->persist($timeEntry);

                // Update the cached spentHours on the todo
                $todo->setSpentHours($todo->getTotalLoggedHours() + $timeEntry->getHours());
                $em->flush();

                $this->addFlash('success', sprintf('Logged %.1fh.', $timeEntry->getHours()));

                return $this->redirectToRoute('app_todo_show', ['id' => $todo->getId()]);
            }
        }

        return $this->render('todo/show.html.twig', [
            'todo' => $todo,
            'project' => $project,
            'canEdit' => $canEdit,
            'timeEntryForm' => $timeEntryForm,
        ]);
    }

    #[Route('/projects/{id}/todos/new', name: 'app_todo_new')]
    public function new(Project $project, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(ProjectPermission::CREATE_TODO->value, $project);

        $todo = new Todo();
        $todo->setProject($project);

        $form = $this->createForm(TodoFormType::class, $todo, ['project' => $project]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($todo);
            $em->flush();

            $this->addFlash('success', 'Todo created.');

            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('todo/form.html.twig', [
            'form' => $form,
            'project' => $project,
            'todo' => null,
        ]);
    }

    #[Route('/todos/{id}/edit', name: 'app_todo_edit')]
    public function edit(Todo $todo, Request $request, EntityManagerInterface $em): Response
    {
        $project = $todo->getProject();
        $this->denyAccessUnlessGranted(ProjectPermission::EDIT_TODO->value, $project);

        $form = $this->createForm(TodoFormType::class, $todo, ['project' => $project]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Todo updated.');

            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('todo/form.html.twig', [
            'form' => $form,
            'project' => $project,
            'todo' => $todo,
        ]);
    }

    #[Route('/todos/{id}/delete', name: 'app_todo_delete', methods: ['POST'])]
    public function delete(Todo $todo, Request $request, EntityManagerInterface $em): Response
    {
        $project = $todo->getProject();
        $this->denyAccessUnlessGranted(ProjectPermission::EDIT_TODO->value, $project);

        if ($this->isCsrfTokenValid('delete-todo-'.$todo->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($todo);
            $em->flush();

            $this->addFlash('success', 'Todo deleted.');
        }

        return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
    }
}
