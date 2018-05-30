<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use \App\Model\LocalReuniaoTable;

class WeeklyMeetingsFactory
{
    public function __invoke(ContainerInterface $container) : WeeklyMeetings
    {
        return new WeeklyMeetings(
            $container->get(TemplateRendererInterface::class),
            $container->get(LocalReuniaoTable::class)
        );
    }
}
