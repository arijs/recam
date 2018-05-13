<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Authentication\AuthenticationService;
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

        return new HtmlResponse($this->template->render('app::login', [
            'baseUrl' => $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class)
        ]));
    }

    public function authenticate(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();
        $username = $params['username'];
        $password = $params['password'];
        $baseUrl = $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class);

        if (empty($username)) {
            return new HtmlResponse($this->template->render('app::login', [
                'baseUrl' => $baseUrl,
                'error' => 'O login não pode estar vazio',
            ]));
        }

        if (empty($password)) {
            return new HtmlResponse($this->template->render('app::login', [
                'baseUrl' => $baseUrl,
                'username' => $username,
                'error'    => 'A senha não pode estar vazia',
            ]));
        }

        $this->authAdapter->setUsername($username);
        $this->authAdapter->setPassword($password);

        $result = $this->auth->authenticate();
        if (!$result->isValid()) {
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
        return new RedirectResponse($baseUrl.'/');
    }
}
