<?php

namespace Hush\ChatBundle\Entity;

use Application\Sonata\MediaBundle\Entity\Media;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as User;
use JsonSerializable;

/**
 * Message
 */
class Message implements JsonSerializable
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var boolean
     */
    private $readed;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var User
     */
    private $recipient;

    /**
     * @var User
     */
    private $sender;


    /**
     * @var Collection
     */
    private $mediaList;


    /**
     * Set text
     *
     * @param string $text
     * @return Message
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Message
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set readed
     *
     * @param boolean $readed
     * @return Message
     */
    public function setReaded($readed)
    {
        $this->readed = $readed;

        return $this;
    }

    /**
     * Get readed
     *
     * @return boolean
     */
    public function isReaded()
    {
        return $this->readed;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set recipient
     *
     * @param User $recipient
     * @return Message
     */
    public function setRecipient(User $recipient = null)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get recipient
     *
     * @return User
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Set sender
     *
     * @param User $sender
     * @return Message
     */
    public function setSender(User $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return User
     */
    public function getSender()
    {
        return $this->sender;
    }


    public function __construct()

    {
        $this->mediaList = new ArrayCollection();
    }

    public function addMessageMedia(MessageMedia $messageMedia)
    {
        $messageMedia->setMessage($this);
        $this->mediaList->add($messageMedia);
    }

    public function getMediaList()
    {
        return $this->mediaList;
    }

    public function removeMessageMedia(MessageMedia $messageMedia)
    {
        $this->mediaList->removeElement($messageMedia);
    }


    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'sender_id' => $this->getSender()->getId(),
            'recipient_id' => $this->getRecipient()->getId(),
            'text' => $this->getText(),
            'date' => $this->getDate()->format('c')
        ];
    }
}
