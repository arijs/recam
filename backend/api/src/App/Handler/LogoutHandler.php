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
use Zend\Session\Container;
// use \App\MyAuthAdapter;

class LogoutHandler implements RequestHandlerInterface
{
    private $auth;
    // private $authAdapter;
    private $template;

    public function __construct(
        TemplateRendererInterface $template,
        AuthenticationService $auth
        // MyAuthAdapter $authAdapter
    ) {
        $this->template    = $template;
        $this->auth        = $auth;
        // $this->authAdapter = $authAdapter;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $session = new Container();
        $session->exchangeArray([]);
        $this->auth->clearIdentity();

        $baseUrl = $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class);
        return new RedirectResponse($baseUrl.'/login');
    }
}
