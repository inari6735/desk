<?php

namespace App\Repository;

use App\Entity\TimeEntry;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeEntry>
 */
class TimeEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeEntry::class);
    }

    /**
     * @return list<array{user: User, totalHours: float}>
     */
    public function findTopContributors(\DateTimeImmutable $from, \DateTimeImmutable $to, int $limit = 3): array
    {
        $rows = $this->createQueryBuilder('te')
            ->select('IDENTITY(te.user) AS userId, SUM(te.hours) AS totalHours')
            ->where('te.date >= :from')
            ->andWhere('te.date <= :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->groupBy('te.user')
            ->orderBy('totalHours', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $em = $this->getEntityManager();
        $result = [];
        foreach ($rows as $row) {
            $user = $em->getRepository(User::class)->find($row['userId']);
            if ($user) {
                $result[] = ['user' => $user, 'totalHours' => (float) $row['totalHours']];
            }
        }

        return $result;
    }

    /**
     * @return list<TimeEntry>
     */
    public function findByUserAndDateRange(User $user, \DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('te')
            ->join('te.todo', 't')
            ->join('t.project', 'p')
            ->addSelect('t', 'p')
            ->where('te.user = :user')
            ->andWhere('te.date >= :from')
            ->andWhere('te.date <= :to')
            ->setParameter('user', $user)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('te.date', 'DESC')
            ->addOrderBy('te.startTime', 'ASC')
            ->addOrderBy('te.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
