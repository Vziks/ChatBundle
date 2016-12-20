<?php
namespace Hush\ChatBundle\Security;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface
{
    protected $userProvider;

    public function __construct(ApiKeyUserProvider $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    public function createToken(Request $request, $providerKey)
    {
        // look for an apikey query parameter
        $apiKey = $request->query->get('apikey');

        //$apiKey = $request->cookies->get('apikey');
        /*
		if (empty($apiKey)){
			$apiKey = $request->headers->get('apikey');
		}
		if (empty($apiKey) && $request->query->has('apikey')) {
			$apiKey = $request->query->get('apikey');
		}
        */

        // or if you want to use an "apikey" header, then do something like this:
        // $apiKey = $request->headers->get('apikey');

        if (!$apiKey) {
            throw new BadCredentialsException('No API key found');
        }

        return new PreAuthenticatedToken('anon.', $apiKey, $providerKey);
    }

    public function authenticateToken(TokenInterface $token,
                                      UserProviderInterface $userProvider, $providerKey)
    {
        $apiKey = $token->getCredentials();
        $username = $this->userProvider->getUsernameForApiKey($apiKey);

        if (!$username) {
            //$token = new \Symfony\Component\Security\Core\Authentication\Token\AnonymousToken();
            return null;
            //throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
            /*
            throw new AuthenticationException(
                    sprintf('API Key "%s" does not exist.', $apiKey));
             */
        }

        $user = $this->userProvider->loadUserByUsername($username);

        return new PreAuthenticatedToken($user, $apiKey, $providerKey,
            $user->getRoles());
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken
        && $token->getProviderKey() === $providerKey;
    }

    public function onAuthenticationFailure(Request $request,
                                            AuthenticationException $exception)
    {
        return new Response("Authentication Failed.", 403);
    }


}

