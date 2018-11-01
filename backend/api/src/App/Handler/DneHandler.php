<?php

declare(strict_types=1);

namespace App\Handler;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Session\Container;
// use \App\MyAuthAdapter;

class DneHandler implements RequestHandlerInterface
{
    private $auth;
    private $dbAdapter;
    // private $template;

    public function __construct(
        AuthenticationService $auth,
        AdapterInterface $dbAdapter
        // MyAuthAdapter $authAdapter
    ) {
        $this->auth        = $auth;
        $this->dbAdapter   = $dbAdapter;
        // $this->authAdapter = $authAdapter;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {

        switch ($request->getAttribute('action', null)) {
            case 'locations':
                return $this->locationsAction($request);
            default:
                // Invalid; thus, a 404!
                return new EmptyResponse(StatusCode::STATUS_NOT_FOUND);
        }
    }

    public function locationsAction(ServerRequestInterface $request) : ResponseInterface
    {
        return new JsonResponse(['locations' => time()]);
    }
}
