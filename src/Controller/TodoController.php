<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Todo;
use App\Enum\ProjectPermission;
use App\Form\TodoFormType;
use App\Repository\TodoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class TodoController extends AbstractController
{
    #[Route('/todos', name: 'app_todos')]
    public function index(TodoRepository $todoRepository): Response
    {
        $todos = $todoRepository->findBy(
            ['assignedTo' => $this->getUser()],
            ['createdAt' => 'DESC'],
        );

        $editableTodos = [];
        foreach ($todos as $todo) {
            if ($todo->getProject() && $this->isGranted(ProjectPermission::EDIT_TODO->value, $todo->getProject())) {
                $editableTodos[$todo->getId()->toRfc4122()] = true;
            }
        }

        return $this->render('todo/index.html.twig', [
            'todos' => $todos,
            'editableTodos' => $editableTodos,
        ]);
    }

    #[Route('/projects/{id}/todos/new', name: 'app_todo_new')]
    public function new(Project $project, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(ProjectPermission::CREATE_TODO->value, $project);

        $todo = new Todo();
        $todo->setProject($project);

        $form = $this->createForm(TodoFormType::class, $todo);
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

        $form = $this->createForm(TodoFormType::class, $todo);
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
