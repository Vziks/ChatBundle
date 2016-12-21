<?php

namespace Hush\ChatBundle\Service;


use Hush\ChatBundle\Entity\Message;
use Sonata\MediaBundle\Model\Media;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;

class MessageSerializer implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __construct($container)
    {
        $this->setContainer($container);
    }

    /**
     * @param $messages
     * @return array
     */
    public function serializeMessages($messages)
    {
        $json = [];
        foreach ($messages as $message) {
            $json[] = $this->serializeMessage($message);
        }
        return $json;
    }

    /**
     * @param Message $message
     * @return array
     */
    public function serializeMessage(Message $message)
    {
        $item = [
            'id' => $message->getId(),
            'sender_id' => $message->getSender()->getId(),
            'recipient_id' => $message->getRecipient()->getId(),
            'text' => $message->getText(),
            'date' => $message->getDate()->format('c')
        ];
        $item['attachments'] = [];
        foreach ($message->getMediaList() as $messageMedia) {
            /**
             * @var Media $media
             */
            $media = $messageMedia->getMedia();
            $provider = $this->container->get($media->getProviderName());
            $pool = $this->container->get('sonata.media.pool');
            $formats = $pool->getContext('message')['formats'];


            $attachment = [
                'original' => [
                    'url' => $this->getRequest()->getUriForPath($provider->generatePublicUrl($media, 'reference')),
                    'width' => $media->getWidth(),
                    'height' => $media->getHeight()
                ]
            ];
            //var_dump($formats);
            foreach ($formats as $name => $format) {
                $attachment[substr($name, strlen('message_'))] = [
                    'url' => $this->getRequest()->getUriForPath($provider->generatePublicUrl($media, $provider->getFormatName($media, $name))),
                    'width' => $format['width'],
                    'height' => floor($media->getHeight() * $format['width'] / $media->getWidth())
                ];
            }

            $item['attachments'][] = $attachment;
        }
        return $item;
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }
}