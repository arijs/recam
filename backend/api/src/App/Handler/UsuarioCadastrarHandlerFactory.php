<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Authentication\AuthenticationService;
use \App\Model\UsuarioTable;
use \App\MyAuthAdapter;

class UsuarioCadastrarHandlerFactory
{
    public function __invoke(ContainerInterface $container) : UsuarioCadastrarHandler
    {
        return new UsuarioCadastrarHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(UsuarioTable::class),
            $container->get(AuthenticationService::class),
            $container->get(MyAuthAdapter::class)
        );
    }
}
