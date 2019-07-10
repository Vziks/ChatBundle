<?php

namespace Hush\ChatBundle\Controller;

use App\Application\Sonata\MediaBundle\Entity\Media;
use Hush\ChatBundle\Entity\Message;
use Hush\ChatBundle\Entity\MessageMedia;
use Hush\ChatBundle\Service\ChatService;
use Hush\ChatBundle\Service\MessageSerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * Chat Rest Api Controller
 * @Route("/admin/api/v1")
 */
class MessageController extends FOSRestController
{

    /**
     * @var ChatService
     */
    protected $chat;

    /**
     * # Возвращает непрочитанные сообщения #
     * ## Ответ в случае успеха ##
     *
     *     {
     *       "success": true,
     *       "data": [
     *           {
     *               "id": 1,
     *               "sender_id": 1,
     *               "sender_name": "Иванов",
     *               "recipient_id": 2,
     *               "recipient_name": "Петров"
     *               "text": "Текст сообщения",
     *               "date": "22.12.2016 14:44:55",
     *               "readed": true,
     *               "attachments": [
     *                   {
     *                       "original": {
     *                           "url": "http://domain/full/path/to/image.jpg",
     *                           "width": 1024,
     *                           "height": 768,
     *                           "size": 623888
     *                       },
     *                       "format1": {
     *                           "url": "http://domain/full/path/to/image_format1.jpg",
     *                           "width": 300,
     *                           "height": 100
     *                       },
     *                       "format2": {
     *                           "url": "http://domain/full/path/to/image_format2.jpg",
     *                           "width": 100,
     *                           "height": 30
     *                       },
     *                       ...
     *                   }
     *               ]
     *           },
     *           ...
     *       ]
     *     }
     * У каждой записи в attachments есть объект original, передающий информацию об исходном изображении.
     * Размер картинки (width, height), размер файла (size), ссылка на оригинальный файл (url).
     * Кроме оригинала добавляется несколько форматов тамбов, настроенных в sonata media bundle для контекста message.
     *
     *
     * ## Ответ в случае ошибки ##
     *
     *     {
     *       "success": false,
     *       "error": "Текст ошибки"
     *     }
     *
     * @Route("/messages/unreaded")
     * @Method("GET")
     * @ApiDoc(
     *     description = "Возвращает непрочитанные сообщения",
     *     section = "ChatBundle"
     * )
     * @View()
     */
    public function getUnreadedMessagesAction()
    {
        $messages = $this->getChat()->getUnreadedMessages($this->getUser());
        return $this->makeMessagesResponse($messages);
    }

    /**
     * # Возвращает список последних сообщений во всех диалогах #
     * ## Ответ в случае успеха ##
     *
     *     {
     *       "success": true,
     *       "data": [
     *           {
     *               "id": 1,
     *               "sender_id": 1,
     *               "sender_name": "Иванов",
     *               "recipient_id": 2,
     *               "recipient_name": "Петров"
     *               "text": "Текст сообщения",
     *               "date": "22.12.2016 14:44:55",
     *               "readed": true,
     *               "attachments": [
     *                   {
     *                       "original": {
     *                           "url": "http://domain/full/path/to/image.jpg",
     *                           "width": 1024,
     *                           "height": 768,
     *                           "size": 623888
     *                       },
     *                       "format1": {
     *                           "url": "http://domain/full/path/to/image_format1.jpg",
     *                           "width": 300,
     *                           "height": 100
     *                       },
     *                       "format2": {
     *                           "url": "http://domain/full/path/to/image_format2.jpg",
     *                           "width": 100,
     *                           "height": 30
     *                       },
     *                       ...
     *                   }
     *               ]
     *           },
     *           ...
     *       ]
     *     }
     * У каждой записи в attachments есть объект original, передающий информацию об исходном изображении.
     * Размер картинки (width, height), размер файла (size), ссылка на оригинальный файл (url).
     * Кроме оригинала добавляется несколько форматов тамбов, настроенных в sonata media bundle для контекста message.
     *
     *
     * ## Ответ в случае ошибки ##
     *
     *     {
     *       "success": false,
     *       "error": "Текст ошибки"
     *     }
     *
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
        return $this->makeMessagesResponse($messages);
    }

    /**
     * # Возвращает список сообщений в диалоге по id собеседника #
     * ## Ответ в случае успеха ##
     *
     *     {
     *       "success": true,
     *       "data": [
     *           {
     *               "id": 1,
     *               "sender_id": 1,
     *               "sender_name": "Иванов",
     *               "recipient_id": 2,
     *               "recipient_name": "Петров"
     *               "text": "Текст сообщения",
     *               "date": "22.12.2016 14:44:55",
     *               "readed": true,
     *               "attachments": [
     *                   {
     *                       "original": {
     *                           "url": "http://domain/full/path/to/image.jpg",
     *                           "width": 1024,
     *                           "height": 768,
     *                           "size": 623888
     *                       },
     *                       "format1": {
     *                           "url": "http://domain/full/path/to/image_format1.jpg",
     *                           "width": 300,
     *                           "height": 100
     *                       },
     *                       "format2": {
     *                           "url": "http://domain/full/path/to/image_format2.jpg",
     *                           "width": 100,
     *                           "height": 30
     *                       },
     *                       ...
     *                   }
     *               ]
     *           },
     *           ...
     *       ]
     *     }
     * У каждой записи в attachments есть объект original, передающий информацию об исходном изображении.
     * Размер картинки (width, height), размер файла (size), ссылка на оригинальный файл (url).
     * Кроме оригинала добавляется несколько форматов тамбов, настроенных в sonata media bundle для контекста message.
     *
     *
     * ## Ответ в случае ошибки ##
     *
     *     {
     *       "success": false,
     *       "error": "Текст ошибки"
     *     }
     *
     * @Route("/messages/dialogs/{id}")
     * @Method("GET")
     * @ApiDoc(
     *     description = "Возвращает список сообщений в диалоге по id собеседника",
     *     section = "ChatBundle",
     *     requirements={
     *         {
     *             "name"="id",
     *             "dataType"="integer",
     *             "requirement"="\d+",
     *             "description"="Id собеседника"
     *         }
     *     }
     * )
     */
    public function getDialogMessagesAction($id)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $collocutor = $userManager->findUserBy(['id' => $id]);
        $messages = $this->getChat()->getDialogMessages($this->getUser(), $collocutor);
        return $this->makeMessagesResponse($messages);
    }

    /**
     * # Отправляет сообщение пользователю #
     * ## Ответ в случае успеха ##
     *
     *     {
     *       "success": true,
     *       "data": [
     *           {
     *               "id": 1,
     *               "sender_id": 1,
     *               "sender_name": "Иванов",
     *               "recipient_id": 2,
     *               "recipient_name": "Петров"
     *               "text": "Текст сообщения",
     *               "date": "22.12.2016 14:44:55",
     *               "readed": true,
     *               "attachments": [
     *                   {
     *                       "original": {
     *                           "url": "http://domain/full/path/to/image.jpg",
     *                           "width": 1024,
     *                           "height": 768,
     *                           "size": 623888
     *                       },
     *                       "format1": {
     *                           "url": "http://domain/full/path/to/image_format1.jpg",
     *                           "width": 300,
     *                           "height": 100
     *                       },
     *                       "format2": {
     *                           "url": "http://domain/full/path/to/image_format2.jpg",
     *                           "width": 100,
     *                           "height": 30
     *                       },
     *                       ...
     *                   }
     *               ]
     *           }
     *       ]
     *     }
     * У каждой записи в attachments есть объект original, передающий информацию об исходном изображении.
     * Размер картинки (width, height), размер файла (size), ссылка на оригинальный файл (url).
     * Кроме оригинала добавляется несколько форматов тамбов, настроенных в sonata media bundle для контекста message.
     *
     *
     * ## Ответ в случае ошибки ##
     *
     *     {
     *       "success": false,
     *       "error": "Текст ошибки"
     *     }
     *
     * @Route("/messages")
     * @Method("POST")
     * @ApiDoc(
     *     description = "Отправляет сообщение пользователю",
     *     section = "ChatBundle",
     *     parameters={
     *         {
     *             "name"="recipient_id",
     *             "dataType"="integer",
     *             "required"=true,
     *             "description"="Id получателя сообщения"
     *         },
     *         {
     *             "name"="message",
     *             "required"=true,
     *             "dataType"="string",
     *             "description"="Текст сообщения"
     *          },
     *         {
     *             "name"="attachment[]",
     *             "required"=false,
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

//        dump($this->getUser());
//        die;


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
        return $this->makeMessagesResponse([$message]);
    }

    /**
     * # Отмечает сообщение как прочитанное #
     * ## Ответ в случае успеха ##
     *
     *     {
     *       "success": true
     *     }
     *
     * ## Ответ в случае ошибки ##
     *
     *     {
     *       "success": false,
     *       "error": "Текст ошибки"
     *     }
     *
     * @Route("/messages/{id}/read")
     * @Method("PUT")
     * @ApiDoc(
     *     description = "Отмечает сообщение как прочитанное",
     *     section = "ChatBundle",
     *     requirements={
     *         {
     *             "name"="id",
     *             "dataType"="integer",
     *             "requirement"="\d+",
     *             "description"="Id сообщения"
     *         }
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
    protected function makeMessagesResponse($messages)
    {
        return new JsonResponse([
            'success' => true,
            'data' => $this->getChat()->serializeMessages($messages)
        ]);
    }

}
