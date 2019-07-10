<?php

namespace Hush\ChatBundle\Entity;

use App\Application\Sonata\MediaBundle\Entity\Media as Media;
use Hush\ChatBundle\Entity\Message as Message;

/**
 * MessageMedia
 */
class MessageMedia
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var Media
     */
    private $media;

    /**
     * @var Message
     */
    private $message;


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
     * Set media
     *
     * @param Media $media
     * @return MessageMedia
     */
    public function setMedia(Media $media = null)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Get media
     *
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Set message
     *
     * @param Message $message
     * @return MessageMedia
     */
    public function setMessage(Message $message = null)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
