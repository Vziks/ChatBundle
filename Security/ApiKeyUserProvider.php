<?php
namespace Hush\ChatBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class ApiKeyUserProvider implements UserProviderInterface
{
    /**
     *
     * @var \FOS\UserBundle\Doctrine\UserManager
     */
    protected $userManager;

    public function __construct($userManager)
    {
        $this->userManager = $userManager;

    }

    public function getUsernameForApiKey($apiKey)
    {
        $user = $this->userManager->findUserByConfirmationToken($apiKey);
        return $user ? $user->getUsernameCanonical() : null;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->userManager->findUserByUsername($username);
        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        // this is used for storing authentication in the session
        // but in this example, the token is sent in each request,
        // so authentication can be stateless. Throwing this exception
        // is proper to make things stateless
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return 'Symfony\Component\Security\Core\User\User' === $class;
    }
}