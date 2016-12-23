<?php


namespace Hush\ChatBundle\Service;


use Hush\ChatBundle\Entity\Message;

interface MessageSerializerInterface
{
    /**
     * @param Message $message
     * @return array
     */
    public function serializeMessage(Message $message);
}