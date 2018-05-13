<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Authentication\AuthenticationService;
// use \App\MyAuthAdapter;

class LogoutHandlerFactory
{
    public function __invoke(ContainerInterface $container) : LogoutHandler
    {
        return new LogoutHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(AuthenticationService::class)
            // $container->get(MyAuthAdapter::class)
        );
    }
}
