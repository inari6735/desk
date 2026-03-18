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
        return $this->createQueryBuilder('pm')
            ->select('p')
            ->join('pm.project', 'p')
            ->where('pm.user = :user')
            ->andWhere('pm.permissions LIKE :permission')
            ->setParameter('user', $user)
            ->setParameter('permission', '%"'.$permission.'"%')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
