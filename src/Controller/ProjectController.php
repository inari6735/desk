<?php

namespace App\Controller;

use App\Entity\Project;
use App\Enum\ProjectPermission;
use App\Repository\ProjectMemberRepository;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function show(Project $project, ProjectMemberRepository $memberRepository): Response
    {
        $this->denyAccessUnlessGranted(ProjectPermission::VIEW->value, $project);

        $membership = $memberRepository->findMembership($project, $this->getUser());

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'canCreateTodo' => $this->isGranted(ProjectPermission::CREATE_TODO->value, $project),
            'canEditTodo' => $this->isGranted(ProjectPermission::EDIT_TODO->value, $project),
        ]);
    }
}
