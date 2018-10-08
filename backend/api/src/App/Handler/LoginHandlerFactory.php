<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Authentication\AuthenticationService;
use \App\MyAuthAdapter;
use \App\Model\UsuarioTable;
use \App\Model\UsuarioFacebookTable;
use \App\Model\UsuarioGoogleTable;
use \App\Model\UsuarioTwitterTable;
use \App\Model\UsuarioLinkedinTable;

class LoginHandlerFactory
{
    public function __invoke(ContainerInterface $container) : LoginHandler
    {
        return new LoginHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(AuthenticationService::class),
            $container->get(MyAuthAdapter::class),
            $container->get(UsuarioTable::class),
            $container->get(UsuarioFacebookTable::class),
            $container->get(UsuarioGoogleTable::class),
            $container->get(UsuarioTwitterTable::class),
            $container->get(UsuarioLinkedinTable::class)
        );
    }
}
