<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use \App\Model\UsuarioTable;

class UsuarioCadastrarHandlerFactory
{
    public function __invoke(ContainerInterface $container) : UsuarioCadastrarHandler
    {
        return new UsuarioCadastrarHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(UsuarioTable::class)
        );
    }
}
