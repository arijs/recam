<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Session\Container;
use \App\MyAuthAdapter;
use \App\Model\Usuario;
use \App\Model\UsuarioTable;
use \App\Model\UsuarioFacebook;
use \App\Model\UsuarioFacebookTable;
use \App\Model\UsuarioGoogle;
use \App\Model\UsuarioGoogleTable;
use \App\Model\UsuarioTwitter;
use \App\Model\UsuarioTwitterTable;
use \App\Model\UsuarioLinkedin;
use \App\Model\UsuarioLinkedinTable;

class LoginHandler implements RequestHandlerInterface
{
     private $template;
     private $auth;
     private $authAdapter;
     private $usuarioTable;
     private $usuarioFacebookTable;
     private $usuarioGoogleTable;
     private $usuarioTwitterTable;
     private $usuarioLinkedinTable;

     public function __construct(
         TemplateRendererInterface $template,
         AuthenticationService $auth,
         MyAuthAdapter $authAdapter,
         UsuarioTable $usuarioTable,
         UsuarioFacebookTable $usuarioFacebookTable,
         UsuarioGoogleTable $usuarioGoogleTable,
         UsuarioTwitterTable $usuarioTwitterTable,
         UsuarioLinkedinTable $usuarioLinkedinTable
     ) {
         $this->template     = $template;
         $this->auth         = $auth;
         $this->authAdapter  = $authAdapter;
         $this->usuarioTable = $usuarioTable;
         $this->usuarioFacebookTable = $usuarioFacebookTable;
         $this->usuarioGoogleTable   = $usuarioGoogleTable;
         $this->usuarioTwitterTable  = $usuarioTwitterTable;
         $this->usuarioLinkedinTable = $usuarioLinkedinTable;
     }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            return $this->authenticate($request);
        }
        $baseUrl = $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class);
        $session = $request->getAttribute(\App\Middleware\InjectAuthMiddleware::class);
        $response = [
            'baseUrl' => $baseUrl,
            'session' => $session,
        ];
        $query = $request->getQueryParams();
        // $returnUrl = (isset($query['return']) ? $query['return'] : 'http://lacolhost.com/api/login');
        $returnUrl = (isset($query['return']) ? $query['return'] : 'https://'.$_SERVER['HTTP_HOST'].'/api/login');
        if (isset($query['authreturn'])) {
            $login = $query['authreturn'];
            if ($login === 'facebook') {
                $sessionContainer = new Container();
                if (empty($query['code'])) {
                    $response['error'] = 'Parâmetro "code" não encontrado';
                } else if (empty($query['state'])) {
                    $response['error'] = 'Parâmetro "state" não encontrado';
                } else if (empty($sessionContainer->authFacebookState)) {
                    $response['error'] = 'Parâmetro "state" não encontrado na sessão';
                } else if ($query['state'] !== $sessionContainer->authFacebookState) {
                    $response['error'] = 'Parâmetro "state" diferente que o armazenado';
                } else {
                    try {
                        $provider = $this->authAdapter->getFacebookProvider($returnUrl.'?authreturn=facebook');
                        $shortToken = $provider->getAccessToken('authorization_code', [
                            'code' => $query['code']
                        ]);
                        $longToken = $provider->getLongLivedAccessToken($shortToken);
                        $user = $provider->getResourceOwner($shortToken);
                        if ($this->auth->hasIdentity()) {
                            $this->authAdapter->setCurrentIdentity($this->auth->getIdentity());
                        }
                        $usuarioFacebook = new UsuarioFacebook();
                        $usuarioFacebook->readFacebookJson($user->toArray());
                        $this->usuarioFacebookTable->saveUsuarioByIdFacebook($usuarioFacebook);
                        $usuario = $this->usuarioTable->getUsuarioFacebook($usuarioFacebook);
                        if (!empty($usuario)) {
                            $this->authAdapter->setUsuario($usuario);
                        }
                        $usuarioArray = $usuarioFacebook->toArray();
                        $usuarioArray['json'] = $user->toArray();
                        $this->authAdapter->setFacebook([
                            'shortToken' => $shortToken->getToken(),
                            'longToken' => $longToken->getToken(),
                            // 'user' => $user->toArray(),
                            'user' => $usuarioArray,
                        ]);
                        $result = $this->auth->authenticate();
                        return new RedirectResponse('/');
                    } catch (Exception $e) {
                        $response['error'] = 'Erro ao pegar os dados do usuário';
                        $response['exception'] = $e->getMessage();
                    }
                }
            } else if ($login === 'google') {
                $sessionContainer = new Container();
                if (empty($query['code'])) {
                    $response['error'] = 'Parâmetro "code" não encontrado';
                } else if (empty($query['state'])) {
                    $response['error'] = 'Parâmetro "state" não encontrado';
                } else if (empty($sessionContainer->authGoogleState)) {
                    $response['error'] = 'Parâmetro "state" não encontrado na sessão';
                } else if ($query['state'] !== $sessionContainer->authGoogleState) {
                    $response['error'] = 'Parâmetro "state" diferente que o armazenado';
                } else {
                    try {
                        $provider = $this->authAdapter->getGoogleProvider($returnUrl.'?authreturn=google');
                        $shortToken = $provider->getAccessToken('authorization_code', [
                            'code' => $query['code']
                        ]);
                        // $longToken = $provider->getLongLivedAccessToken($shortToken);
                        $user = $provider->getResourceOwner($shortToken);
                        if ($this->auth->hasIdentity()) {
                            $this->authAdapter->setCurrentIdentity($this->auth->getIdentity());
                        }
                        $usuarioGoogle = new UsuarioGoogle();
                        $usuarioGoogle->readGoogleJson($user->toArray());
                        $this->usuarioGoogleTable->saveUsuarioByIdGoogle($usuarioGoogle);
                        $usuario = $this->usuarioTable->getUsuarioGoogle($usuarioGoogle);
                        if (!empty($usuario)) {
                            $this->authAdapter->setUsuario($usuario);
                        }
                        $usuarioArray = $usuarioGoogle->toArray();
                        $usuarioArray['json'] = $user->toArray();
                        $this->authAdapter->setGoogle([
                            'shortToken' => $shortToken->getToken(),
                            // 'longToken' => $longToken->getToken(),
                            'user' => $usuarioArray,
                        ]);
                        $result = $this->auth->authenticate();
                        return new RedirectResponse('/');
                    } catch (Exception $e) {
                        $response['error'] = 'Erro ao pegar os dados do usuário';
                        $response['exception'] = $e->getMessage();
                    }
                }
            } else if ($login === 'twitter') {
                $sessionContainer = new Container();
                $sessionTwitter = $sessionContainer->authTwitterState;
                $twitterToken = isset($sessionTwitter['oauth_token'])
                    ? $sessionTwitter['oauth_token'] : null;
                if (empty($query['oauth_token'])) {
                    $response['error'] = 'Parâmetro "oauth_token" não encontrado';
                } else if (empty($query['oauth_verifier'])) {
                    $response['error'] = 'Parâmetro "oauth_verifier" não encontrado';
                } else if (empty($twitterToken)) {
                    $response['error'] = 'Parâmetro "oauth_token" não encontrado na sessão';
                } else if ($query['oauth_token'] !== $twitterToken) {
                    $response['error'] = 'Parâmetro "oauth_token" diferente que o armazenado';
                } else {
                    try {
                        $provider = $this->authAdapter->getTwitterProvider();
                        $provider->setOauthToken($sessionTwitter['oauth_token'], $sessionTwitter['oauth_token_secret']);
                        // $response['twitter_session'] = print_r($sessionTwitter, true);
                        $accessToken = $provider->oauth("oauth/access_token", ["oauth_verifier" => $query['oauth_verifier']]);
                        // $response['twitter_access_token'] = print_r($accessToken, true);
                        $sessionContainer->authTwitterState = [
                            'oauth_token' => $sessionTwitter['oauth_token'],
                            'oauth_token_secret' => $sessionTwitter['oauth_token_secret'],
                            'access_token' => $accessToken['oauth_token'],
                            'access_token_secret' => $accessToken['oauth_token_secret'],
                        ];
                        // $sessionTwitter['oauth_token'] = $accessToken['oauth_token'];
                        // $sessionTwitter['oauth_token_secret'] = $accessToken['oauth_token_secret'];
                        // $sessionContainer->authTwitterState = $sessionTwitter; // só pra garantir
                        $provider->setOauthToken($accessToken['oauth_token'], $accessToken['oauth_token_secret']);
                        $user = $provider->get("account/verify_credentials", ["include_email" => true]);
                        // $longToken = $provider->getLongLivedAccessToken($shortToken);
                        if ($this->auth->hasIdentity()) {
                            $this->authAdapter->setCurrentIdentity($this->auth->getIdentity());
                        }
                        $usuarioTwitter = new UsuarioTwitter();
                        $usuarioTwitter->readTwitterJson($user);
                        $this->usuarioTwitterTable->saveUsuarioByIdTwitter($usuarioTwitter);
                        $usuario = $this->usuarioTable->getUsuarioTwitter($usuarioTwitter);
                        if (!empty($usuario)) {
                            $this->authAdapter->setUsuario($usuario);
                        }
                        $usuarioArray = $usuarioTwitter->toArray();
                        $usuarioArray['json'] = $user;
                        $this->authAdapter->setTwitter([
                            'oauth_token' => $sessionTwitter['oauth_token'],
                            'access_token' => $accessToken['oauth_token'],
                            // 'oauth_token_secret' => $accessToken['oauth_token_secret'],
                            'user' => $usuarioArray,
                        ]);
                        $result = $this->auth->authenticate();
                        return new RedirectResponse('/');
                    } catch (Exception $e) {
                        $response['error'] = 'Erro ao pegar os dados do usuário';
                        $response['exception'] = $e->getMessage();
                    }
                }
            } else if ($login === 'linkedin') {
                $sessionContainer = new Container();
                if (empty($query['code'])) {
                    $response['error'] = 'Parâmetro "code" não encontrado';
                } else if (empty($query['state'])) {
                    $response['error'] = 'Parâmetro "state" não encontrado';
                } else if (empty($sessionContainer->authLinkedinState)) {
                    $response['error'] = 'Parâmetro "state" não encontrado na sessão';
                } else if ($query['state'] !== $sessionContainer->authLinkedinState) {
                    $response['error'] = 'Parâmetro "state" diferente que o armazenado';
                } else {
                    try {
                        $provider = $this->authAdapter->getLinkedinProvider($returnUrl.'?authreturn=linkedin');
                        $shortToken = $provider->getAccessToken('authorization_code', [
                            'code' => $query['code']
                        ]);
                        // $longToken = $provider->getLongLivedAccessToken($shortToken);
                        $user = $provider->getResourceOwner($shortToken);
                        if ($this->auth->hasIdentity()) {
                            $this->authAdapter->setCurrentIdentity($this->auth->getIdentity());
                        }
                        $usuarioLinkedin = new UsuarioLinkedin();
                        $usuarioLinkedin->readLinkedinJson($user->toArray());
                        $this->usuarioLinkedinTable->saveUsuarioByIdLinkedin($usuarioLinkedin);
                        $usuario = $this->usuarioTable->getUsuarioLinkedin($usuarioLinkedin);
                        if (!empty($usuario)) {
                            $this->authAdapter->setUsuario($usuario);
                        }
                        $usuarioArray = $usuarioLinkedin->toArray();
                        $usuarioArray['json'] = $user->toArray();
                        $this->authAdapter->setLinkedin([
                            'shortToken' => $shortToken->getToken(),
                            // 'longToken' => $longToken->getToken(),
                            'user' => $usuarioArray,
                        ]);
                        $result = $this->auth->authenticate();
                        return new RedirectResponse('/');
                    } catch (Exception $e) {
                        $response['error'] = 'Erro ao pegar os dados do usuário';
                        $response['exception'] = $e->getMessage();
                    }
                }
            }
        } else {
            $sessionContainer = new Container();

            $facebook = $this->authAdapter->initFacebook($returnUrl.'?authreturn=facebook');
            $response['facebook'] = $facebook['auth_url'];
            $sessionContainer->authFacebookState = $facebook['state'];

            $google = $this->authAdapter->initGoogle($returnUrl.'?authreturn=google');
            $response['google'] = $google['auth_url'];
            $sessionContainer->authGoogleState = $google['state'];

            $twitter = $this->authAdapter->initTwitter($returnUrl.'?authreturn=twitter');
            $response['twitter'] = $twitter['auth_url'];
            $sessionContainer->authTwitterState = [
                'oauth_token' => $twitter['oauth_token'],
                'oauth_token_secret' => $twitter['oauth_token_secret'],
            ];

            $linkedin = $this->authAdapter->initLinkedin($returnUrl.'?authreturn=linkedin');
            $response['linkedin'] = $linkedin['auth_url'];
            $sessionContainer->authLinkedinState = $linkedin['state'];

            // $github = $this->authAdapter->initGithub($returnUrl.'?authreturn=github');
            // $response['github'] = $github['auth_url'];
            // $sessionContainer->authGithubState = $github['state'];

            // $paypal = $this->authAdapter->initPaypal($returnUrl.'?authreturn=paypal');
            // $response['paypal'] = $paypal['auth_url'];
            // $sessionContainer->authPaypalState = $paypal['state'];
        }
        if (!empty($response['session'])) {
            $response['session'] = $this->authAdapter->sessionObjectToArray($response['session']);
        }
        return new JsonResponse($response);

        // return new HtmlResponse($this->template->render('app::login', [
        //     'baseUrl' => $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class)
        // ]));
    }

    public function authenticate(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();
        $username = $params['username'];
        $password = $params['password'];
        $error_username = [];
        $error_password = [];

        if (empty($username)) {
            $error_username[] = 'O login não pode estar vazio';
        }

        if (empty($password)) {
            $error_password[] = 'A senha não pode estar vazia';
        }

        $session = null;
        $tried = false;
        if (empty($error_username) && empty($error_password)) {
            $this->authAdapter->setAccount($username, $password);

            $result = $this->auth->authenticate();
            $tried = true;
            if ($result->isValid()) {
                $session = $result->getIdentity();
            } else {
                $error_password = $result->getMessages();
            }
        }
        $errors = [];
        if (!empty($error_username)) $errors['username'] = $error_username;
        if (!empty($error_password)) $errors['password'] = $error_password;
        return new JsonResponse([
            'baseUrl' => $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class),
            'session' => $this->authAdapter->sessionObjectToArray($session),
            'errorFields' => empty($errors) ? null : $errors,
        ]);
        /* if (!$result->isValid()) {
            $msgs = $result->getMessages();
            if (empty($msgs)) {
                $msgs = 'Usuario ou senha inválidos';
            } else {
                $msgs = implode(' / ', $msgs);
            }
            return new HtmlResponse($this->template->render('app::login', [
                'baseUrl' => $baseUrl,
                'username' => $username,
                'error'    => $msgs,
            ]));
        }
        return new RedirectResponse($baseUrl.'/'); */
    }

}
