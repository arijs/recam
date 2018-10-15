<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Authentication\AuthenticationService;
use \App\Model\UsuarioTable;
use \App\Model\LocalReuniaoTable;
use \App\MyAuthAdapter;

class UsuarioHandlerFactory
{
    public function __invoke(ContainerInterface $container, $class, array $options = null)
    {
        return new $class(
            $container->get(TemplateRendererInterface::class),
            $container->get(UsuarioTable::class),
            $container->get(LocalReuniaoTable::class),
            $container->get(AuthenticationService::class),
            $container->get(MyAuthAdapter::class)
        );
    }
}
