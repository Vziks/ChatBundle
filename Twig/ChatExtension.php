<?php

namespace Hush\ChatBundle\Twig;


use FOS\UserBundle\Model\UserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Hush\ChatBundle\Service\ChatService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ChatExtension
 * @author Anton Prokhorov <vziks@live.ru>
 */
class ChatExtension extends AbstractExtension implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /** @var TokenStorageInterface */
    private $tokenStorage;    

    /**
     * ChatExtension constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }


    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('unreadMessage', [$this, 'unreadMessage']),
        ];
    }

    public function unreadMessage($id)
    {
        $collocutor = $this->container->get("fos_user.user_manager")->findUserBy(['id' => $id]);
        $result = $this->getChat()->getUnreadedMessagesByUser($this->getUser(), $collocutor);

        dump($this->getUser());
        dump($result);
        return $result;
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
     * @return User|null
     */
    private function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }
}