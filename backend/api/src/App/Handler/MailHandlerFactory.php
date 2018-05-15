<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;

class MailHandlerFactory
{
    public function __invoke(ContainerInterface $container) : MailHandler
    {
        // Setup SMTP transport
        $config = $container->get('config');
        $mail = $config['mail']['']['sistema'];
        $transport = new Smtp();
        $options   = new SmtpOptions($mail['smtpOptions']);
        $transport->setOptions($options);
        return new MailHandler(
            $container->get(TemplateRendererInterface::class),
            $transport,
            $mail['from'],
            $config['mail']['_info']
        );
    }
}
