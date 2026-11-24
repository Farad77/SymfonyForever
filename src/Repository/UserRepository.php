<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByUsernameOrEmail(string $username, string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.username) = LOWER(:username) OR LOWER(u.email) = LOWER(:email)')
            ->setParameter('username', $username)
            ->setParameter('email', $email)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function loadUserByIdentifier(string $identifier): ?User
    {
        $users = $this->createQueryBuilder('u')
            ->where('LOWER(u.username) = LOWER(:username)')
            ->setParameter('username', $identifier)
            ->setMaxResults(2)
            ->getQuery()
            ->getResult();

        if (count($users) > 1) {
            throw new AuthenticationServiceException('Multiple users have the same case-insensitive username.');
        }

        return $users[0] ?? null;
    }
}
