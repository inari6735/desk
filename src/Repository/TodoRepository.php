<?php

namespace App\Repository;

use App\Entity\Todo;
use App\Entity\User;
use App\Enum\TodoStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Todo>
 */
class TodoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Todo::class);
    }

    public function createUserTodosQueryBuilder(
        User $user,
        ?string $search = null,
        ?TodoStatus $status = null,
        ?Uuid $projectId = null,
        string $sort = 'createdAt',
        string $direction = 'DESC',
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('t')
            ->join('t.project', 'p')
            ->addSelect('p')
            ->where('t.assignedTo = :user')
            ->setParameter('user', $user);

        if ($search) {
            $qb->andWhere('LOWER(t.title) LIKE LOWER(:search) OR LOWER(t.description) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($status) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $status->value);
        }

        if ($projectId) {
            $qb->andWhere('t.project = :project')
                ->setParameter('project', $projectId);
        }

        $allowedSorts = ['createdAt', 'dueDate', 'title', 'status'];
        $sortField = in_array($sort, $allowedSorts, true) ? $sort : 'createdAt';
        $dir = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $qb->orderBy('t.' . $sortField, $dir);

        return $qb;
    }

    /**
     * @return array{items: list<Todo>, total: int, pages: int}
     */
    public function paginateUserTodos(
        User $user,
        int $page = 1,
        int $limit = 15,
        ?string $search = null,
        ?TodoStatus $status = null,
        ?Uuid $projectId = null,
        string $sort = 'createdAt',
        string $direction = 'DESC',
    ): array {
        $qb = $this->createUserTodosQueryBuilder($user, $search, $status, $projectId, $sort, $direction);

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(t.id)')->resetDQLPart('orderBy')->getQuery()->getSingleScalarResult();
        $pages = max(1, (int) ceil($total / $limit));
        $page = max(1, min($page, $pages));

        $items = $qb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'items' => $items,
            'total' => $total,
            'pages' => $pages,
        ];
    }
}
