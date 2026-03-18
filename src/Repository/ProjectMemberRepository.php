<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\ProjectMember;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectMember>
 */
class ProjectMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectMember::class);
    }

    public function findMembership(Project $project, User $user): ?ProjectMember
    {
        return $this->findOneBy(['project' => $project, 'user' => $user]);
    }

    /**
     * @return list<Project>
     */
    public function findProjectsForUser(User $user, string $permission): array
    {
        $memberships = $this->findBy(['user' => $user]);

        $projects = [];
        foreach ($memberships as $membership) {
            if ($membership->hasPermission($permission)) {
                $projects[] = $membership->getProject();
            }
        }

        usort($projects, fn (Project $a, Project $b) => $b->getCreatedAt() <=> $a->getCreatedAt());

        return $projects;
    }
}
