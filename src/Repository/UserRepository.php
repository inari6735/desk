<?php

namespace App\Repository;

use App\Entity\User;
use App\Enum\Position;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @return list<User>
     */
    public function findEmployees(?Position $position = null): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT id FROM "user" WHERE roles::text LIKE :role';
        $params = ['role' => '%"ROLE_EMPLOYEE"%'];

        if ($position) {
            $sql .= ' AND position = :position';
            $params['position'] = $position->value;
        }

        $sql .= ' ORDER BY name ASC';

        $ids = $conn->executeQuery($sql, $params)->fetchFirstColumn();

        if (empty($ids)) {
            return [];
        }

        return $this->createQueryBuilder('u')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
