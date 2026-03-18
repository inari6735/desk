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
