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
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Chat Rest Api Controller
 */
class MessageController extends Controller
{

    /**
     * @var ChatService
     */
    protected $chat;

    /**
     * @Route("/messages/unreaded")
     * @Method("GET")
     * @ApiDoc(
     *     description = "Возвращает непрочитанные сообщения авторизованного пользователя",
     *     section = "ChatBundle"
     * )
     */
    public function getUnreadedMessagesAction()
    {
        $messages = $this->getChat()->getUnreadedMessages($this->getUser());
        return $this->makeResponse($messages);
    }

    /**
     * @Route("/messages/last")
     * @Method("GET")
     * @ApiDoc(
     *     description = "Возвращает список последних сообщений во всех диалогах",
     *     section = "ChatBundle"
     * )
     */
    public function getLastMessagesAction()
    {
        $messages = $this->getChat()->getLastMessages($this->getUser());
        return $this->makeResponse($messages);
    }

    /**
     * @Route("/messages/dialogs/{id}")
     * @Method("GET")
     * @ApiDoc(
     *     description = "Возвращает список сообщений в диалоге по id собеседника",
     *     section = "ChatBundle"
     * )
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
     * @ApiDoc(
     *     description = "Отправляет сообщение пользователю",
     *     section = "ChatBundle",
     *     requirements={
     *         {
     *             "name"="recipient_id",
     *             "dataType"="integer",
     *             "requirement"="\d+",
     *             "description"="Id получателя сообщения"
     *         },
     *         {
     *             "name"="message",
     *             "dataType"="string",
     *             "description"="Текст сообщения"
     *          },
     *         {
     *             "name"="attachment[]",
     *             "dataType"="file",
     *             "description"="Файлы картинок в сообщении"
     *          },
     *     },
     * )
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
     * @Method("PUT")
     * @ApiDoc(
     *     description = "Отмечает сообщение как прочитанное",
     *     section = "ChatBundle",
     *     parameters={
     *         {"name"="id", "dataType"="integer", "required"=true, "description"="message id"}
     *     }
     * )
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
