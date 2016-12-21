<?php

namespace Hush\ChatBundle\Controller;

use Application\Sonata\MediaBundle\Entity\Media;
use Hush\ChatBundle\Entity\Message;
use Hush\ChatBundle\Entity\MessageMedia;
use Hush\ChatBundle\Service\ChatService;
use Hush\ChatBundle\Service\MessageSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MessageController
 * @package Hush\ChatBundle\Controller
 *
 * php app/console sonata:media:fix-media-context
 * php_fileinfo extension
 */
class MessageController extends Controller
{

    /**
     * @var ChatService
     */
    protected $chat;

    /**
     * @Route("/messages/unreaded")
     */
    public function unreadedMessagesAction()
    {
        $messages = $this->getChat()->getUnreadedMessages($this->getUser());
        return $this->makeResponse($messages);
    }

    /**
     * @Route("/messages/last")
     */
    public function dialogsAction()
    {
        $messages = $this->getChat()->getLastMessages($this->getUser());
        return $this->makeResponse($messages);
    }

    /**
     * @Route("/messages/dialogs/{id}")
     */
    public function dialogMessagesAction($id)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $collocutor = $userManager->findUserBy(['id' => $id]);
        $messages = $this->getChat()->getDialogMessages($this->getUser(), $collocutor);
        return $this->makeResponse($messages);
    }

    /**
     * @Route("/messages")
     * @Method("POST")
     */
    public function sendMessageAction(Request $request)
    {
        $recipient_id = $request->request->get('recipient_id');
        $text = $request->request->get('message');
        $userManager = $this->container->get('fos_user.user_manager');


        $recipient = $userManager->findUserBy(['id' => $recipient_id]);
        $message = $this->getChat()->sendMessage($this->getUser(), $recipient, $text);

        if ($request->files->has('attachment')) {
            /**
             * @var UploadedFile $upload
             */
            foreach ($request->files->get('attachment') as $upload) {
                //var_dump($upload);
                $mediaManager = $this->container->get('sonata.media.manager.media');
                $media = new Media();
                $media->setBinaryContent($upload);
                $mediaManager->save($media, 'message', 'sonata.media.provider.image');
                $messageMedia = new MessageMedia();
                $messageMedia->setMedia($media);
                $message->addMessageMedia($messageMedia);

            }
        }
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse();
    }

    /**
     * @Route("/messages/{id}/read")
     */
    public function readMessageAction($id)
    {
        /**
         * @var Message $message
         */
        $message = $this->getDoctrine()->getManager()->getRepository('ChatBundle:Message')->findOneBy([
            'id' => $id,
            'recipient' => $this->getUser()
        ]);
        $this->getChat()->readMessage($message);
        return new JsonResponse();
    }

    /**
     * @Route("/messages/test")
     */
    public function testAction()
    {
        $message = $this->getDoctrine()->getRepository('ChatBundle:Message')->find(2);
        $media = $this->getDoctrine()->getRepository('ApplicationSonataMediaBundle:Media')->find(8);
        $messageMedia = new MessageMedia();
        $messageMedia->setMedia($media);
        $message->addMessageMedia($messageMedia);
        $this->getDoctrine()->getManager()->persist($message);
        $this->getDoctrine()->getManager()->flush();


        /*
        $mediaManager = $this->container->get('sonata.media.manager.media');
        $media = new Media();
        $media->setBinaryContent('d:\Hush\test.png');
        $mediaManager->save($media, 'message', 'sonata.media.provider.image');
        die('test');
        */
    }

    /**
     * @Route("/messages/{id}")
     */
    public function getMessageAction($id)
    {

    }

    /**
     * @return ChatService
     */
    protected function getChat()
    {
        if (empty($this->chat)) {
            $this->chat = new ChatService($this->container);
        }
        return $this->chat;
    }

    /**
     * @param $messages
     * @return JsonResponse
     */
    protected function makeResponse($messages)
    {
        $serializer = new MessageSerializer($this->container);
        $json = $serializer->serializeMessages($messages);
        return new JsonResponse($json);
    }

}
