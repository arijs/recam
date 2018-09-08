<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use \App\Model\LocalReuniaoTable;

class WeeklyMeetingsFactory
{
    public function __invoke(ContainerInterface $container, $handlerClass, array $options = null)
    {
        return new $handlerClass(
            $container->get(TemplateRendererInterface::class),
            $container->get(LocalReuniaoTable::class)
        );
    }
}
