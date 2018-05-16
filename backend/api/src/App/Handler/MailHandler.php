<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mail\Message;

class MailHandler implements RequestHandlerInterface
{
    /**
     * @var TemplateRendererInterface
     */
    private $renderer;
    private $mailTransport;
    private $mailSender;
    private $info;

    public function __construct(
        TemplateRendererInterface $renderer,
        TransportInterface $transport,
        array $sender,
        array $info
    )
    {
        $this->renderer = $renderer;
        $this->mailTransport = $transport;
        $this->mailSender = $sender;
        $this->info = $info;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        // Do some work...
        $sender = $this->mailSender;
        $message = new Message();
        $message->setEncoding('UTF-8');
        $message->addTo('rhengles@gmail.com');
        $message->addFrom($sender['address'], $sender['name']);
        $message->setSubject('Teste Email Registro de Campo');
        $message->setBody(
            "Corpo da mensagem - teste\nteste 1\nteste 2\n\nteste 3\n\nteste 4\nteste 5\nteste 6\n\n" .
            print_r($this->info, true)
        );
        $this->mailTransport->send($message);

        // Render and return a response:
        return new HtmlResponse($this->renderer->render(
            'app::mail',
            [] // parameters to pass to template
        ));
    }
}
