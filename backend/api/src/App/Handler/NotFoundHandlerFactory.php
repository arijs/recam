<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Authentication\AuthenticationService;
// use \App\MyAuthAdapter;

class NotFoundHandlerFactory
{
    public function __invoke(ContainerInterface $container) : NotFoundHandler
    {
        return new NotFoundHandler(
            function() {
                return new Response(new Stream('php://temp', 'wb+'));
            },
            $container->get(TemplateRendererInterface::class)
        );
    }
}
