<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;

class InjectBaseUrlMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : InjectBaseUrlMiddleware
    {
        return new InjectBaseUrlMiddleware(
            $container->get('config')['los_basepath']
        );
    }
}
