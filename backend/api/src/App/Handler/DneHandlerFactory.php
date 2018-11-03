<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Adapter\AdapterInterface;

class DneHandlerFactory
{
    public function __invoke(ContainerInterface $container) : DneHandler
    {
        return new DneHandler(
            $container->get(AuthenticationService::class),
            $container->get(AdapterInterface::class)
        );
    }
}
