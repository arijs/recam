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

class LoginHandler implements RequestHandlerInterface
{
     private $template;
     private $auth;
     private $authAdapter;
     private $usuarioTable;

     public function __construct(
         TemplateRendererInterface $template,
         AuthenticationService $auth,
         MyAuthAdapter $authAdapter,
         UsuarioTable $usuarioTable
     ) {
         $this->template     = $template;
         $this->auth         = $auth;
         $this->authAdapter  = $authAdapter;
         $this->usuarioTable = $usuarioTable;
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
        $returnUrl = (isset($query['return']) ? $query['return'] : 'http://'.$_SERVER['HTTP_HOST'].'/api/login').'?authreturn=facebook';
        if (isset($query['auth'])) {
            $login = $query['auth'];
            if ($login === 'facebook') {
                $app = $this->authAdapter->initFacebook($returnUrl);
                $response['facebook'] = $app['auth_url'];
                $sessionContainer = new Container();
                $sessionContainer->authFacebookState = $app['state'];
            }
        } else if (isset($query['authreturn'])) {
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
                        $provider = $this->authAdapter->getFacebookProvider($returnUrl);
                        $shortToken = $provider->getAccessToken('authorization_code', [
                            'code' => $query['code']
                        ]);
                        $longToken = $provider->getLongLivedAccessToken($shortToken);
                        $user = $provider->getResourceOwner($shortToken);
                        if ($this->auth->hasIdentity()) {
                            $this->authAdapter->setCurrentIdentity($this->auth->getIdentity());
                        }
                        $this->authAdapter->setFacebook([
                            'shortToken' => $shortToken->getToken(),
                            'longToken' => $longToken->getToken(),
                            'user' => $user->toArray(),
                        ]);
                        $result = $this->auth->authenticate();
                        return new RedirectResponse('/');
                    } catch (Exception $e) {
                        $response['error'] = 'Erro ao pegar os dados do usuário';
                        $response['exception'] = $e->getMessage();
                    }
                }
            }
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
            $this->authAdapter->setAccount(array(
                'username' => $username,
                'password' => $password,
            ));

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
            'baseUrl' => $request->getAttribute(\App\Middleware\InjectAuthMiddleware::class),
            'session' => $session,
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
