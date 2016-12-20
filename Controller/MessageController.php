<?php

namespace Hush\ChatBundle\Controller;

use Hush\ChatBundle\Entity\Message;
use Hush\ChatBundle\Service\ChatService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
        $messages = $this->getChat()->getDialogs($this->getUser());
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

        $this->getChat()->sendMessage($this->getUser(), $recipient, $text);
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

    protected function makeResponse($data)
    {
        return new JsonResponse($data);
    }

}
