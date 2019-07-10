<?php

namespace Hush\ChatBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Hush\ChatBundle\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use FOS\UserBundle\Model\User as User;

/**
 * Class MessageRepository
 * Project platform
 * @author Anton Prokhorov <vziks@live.ru>
 */
class MessageRepository extends EntityRepository
{
    /**
     * @param User $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getChatContacts(User $user)
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->where($qb->expr()->eq('m.recipient', ':user'))
            ->setParameter('user', $user);
        return $qb;
    }


}