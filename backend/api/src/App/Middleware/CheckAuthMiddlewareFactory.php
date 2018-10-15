<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;

class CheckAuthMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : CheckAuthMiddleware
    {
        return new CheckAuthMiddleware(
            $container->get(AuthenticationService::class)
        );
    }
}
