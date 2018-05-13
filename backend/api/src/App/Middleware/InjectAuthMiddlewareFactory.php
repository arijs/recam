<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;

class InjectAuthMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : InjectAuthMiddleware
    {
        return new InjectAuthMiddleware($container->get(AuthenticationService::class));
    }
}
