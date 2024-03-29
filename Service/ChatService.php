<?php

namespace Hush\ChatBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Query\ResultSetMapping;
use Hush\ChatBundle\Entity\Message;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use FOS\UserBundle\Model\User as User;

class ChatService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __construct($container)
    {
        $this->setContainer($container);
    }

    /**
     * Возвращает список непрочитанных сообщений пользователя
     *
     * @param User $user
     *
     * @return array
     */
    public function getUnreadedMessages(User $user)
    {
        $query = $this->getDoctrine()->getManager()->createQuery(
            'select m from ChatBundle:Message m where
              (m.recipient=:user and m.readed=0)
              order by m.date desc
              '
        )->setParameter('user', $user);
        return $query->getResult();
    }


    public function getUnreadedMessagesByUser(User $user, User $collocutor)
    {

        $query = $this->getDoctrine()->getManager()->createQuery(
            'select count(m) from ChatBundle:Message m where
              (m.recipient=:user and m.readed=0 and m.sender=:collocutor)
              order by m.date desc
              '
        )->setParameter('user', $user)
            ->setParameter('collocutor', $collocutor);
        return $query->setMaxResults(1)->getSingleScalarResult();
    }


    public function readAllMessageFromSender(User $user, User $collocutor)
    {

        $query = $this->getDoctrine()->getManager()->createQuery(
            'UPDATE ChatBundle:Message m SET m.readed = 1 WHERE (m.recipient=:user and m.readed=0 and m.sender=:collocutor)'
        )->setParameter('user', $user)
            ->setParameter('collocutor', $collocutor);
        return $query->execute();
    }

    /**
     * Возвращает список непрочитанных сообщений пользователя
     *
     * @param User $user
     *
     * @return array
     */
    public function getCountUnreadedMessages(User $user)
    {
        $query = $this->getDoctrine()->getManager()->createQuery(
            'select count(m) from ChatBundle:Message m where
              (m.recipient=:user and m.readed=0)
              order by m.date desc
              '
        )->setParameter('user', $user);
        return $query->getResult();
    }

    /**
     * Возвращает по одному последнему сообщению из всех диалогов пользователя
     *
     * @param User $user
     *
     * @return array
     */
    public function getLastMessages(User $user)
    {
        $sql = '
          select id from
            (
                select m.id,
                (case when m.sender_id=:user_id then m.recipient_id else m.sender_id end) as dialog
                from message m where (sender_id=:user_id or recipient_id=:user_id)
                order by date desc
            ) a
          group by a.dialog
        ';
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $result = $this->getDoctrine()->getManager()->createNativeQuery($sql, $rsm)
            ->setParameter('user_id', $user->getId())
            ->getResult();
        $list = [];
        foreach ($result as $row) {
            $list[] = $row['id'];
        }
        if (!empty($list)) {
            return $this->getDoctrine()->getManager()->createQuery('select m from ChatBundle:Message m where m.id in (:list) order by m.date desc')
                ->setParameter('list', $list)->getResult();
        } else {
            return [];
        }
    }

    /**
     * Возвращает переписку пользователя $user с $collocutor
     *
     * @param User $user
     * @param User $collocutor
     */
    public function getDialogMessages(User $user, User $collocutor)
    {
        $query = $this->getDoctrine()->getManager()->createQuery('
          select m from ChatBundle:Message m where
          (m.sender=:user and m.recipient=:collocutor)
          or (m.sender=:collocutor and m.recipient=:user)
          order by m.date ASC
        ')
            ->setParameter('user', $user)
            ->setParameter('collocutor', $collocutor);
        return $query->getResult();
    }

    public function getChatContacts(User $user)
    {
        $query = $this->getDoctrine()->getManager()->createQuery('
          select m from ChatBundle:Message m where
          m.sender=:user
          or m.recipient=:user
          group by m.recipient, m.sender
          order by m.date desc
        ')
            ->setParameter('user', $user);

        return $query->getResult();
    }

    /**
     * Отправляет сообщение
     *
     * @param User $user
     * @param User $recipient
     * @param      $text
     *
     * @return Message
     */
    public function sendMessage(User $user, User $recipient, $text)
    {
        $message = new Message();
        $message->setSender($user);
        $message->setRecipient($recipient);
        $message->setText($text);
        $message->setDate(new \DateTime('now'));
        $message->setReaded(false);
        $this->getDoctrine()->getManager()->persist($message);
        $this->getDoctrine()->getManager()->flush();
        return $message;
    }

    public function attachImageToMessage(Message $message, $image)
    {

    }

    /**
     * Отмечает сообщение как прочитанное
     *
     * @param Message $message
     */
    public function readMessage(Message $message)
    {
        $message->setReaded(true);
        $this->getDoctrine()->getManager()->persist($message);
        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * @param $messages
     *
     * @return array
     */
    public function serializeMessages($messages)
    {
        $serializer = $this->getMessageSerializer();
        $json = [];
        foreach ($messages as $message) {
            $json[] = $serializer->serializeMessage($message);
        }
        return $json;
    }

    /**
     * @return MessageSerializerInterface
     */
    protected function getMessageSerializer()
    {
        return $this->container->get($this->container->getParameter('chat.message.serializer'));
    }

    /**
     * @return Registry
     */
    protected function getDoctrine()
    {
        return $this->container->get('doctrine');
    }

}