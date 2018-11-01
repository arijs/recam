<?php

declare(strict_types=1);

namespace App\Handler;

use Zend\Expressive\Handler\NotFoundHandler as ZendNotFoundHandler;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Session\Container;
use App\Middleware\InjectAuthMiddleware;
use App\Middleware\InjectBaseUrlMiddleware;
// use \App\MyAuthAdapter;

class NotFoundHandler extends ZendNotFoundHandler
{
    public const TEMPLATE_DEFAULT = 'error::404';
    public const LAYOUT_DEFAULT = 'layout::default';

    /**
     * @var TemplateRendererInterface|null
     */
    private $renderer;

    /**
     * @var callable
     */
    private $responseFactory;

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $layout;

    public function __construct(
        callable $responseFactory,
        TemplateRendererInterface $renderer = null,
        string $template = self::TEMPLATE_DEFAULT,
        string $layout = self::LAYOUT_DEFAULT
    ) {
        // Factory cast to closure in order to provide return type safety.
        $this->responseFactory = function () use ($responseFactory) : ResponseInterface {
            return $responseFactory();
        };
        $this->renderer = $renderer;
        $this->template = $template;
        $this->layout = $layout;
    }

    /**
     * Creates and returns a 404 response.
     *
     * @param ServerRequestInterface $request Passed to internal handler
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        if ($this->renderer === null) {
            return $this->generatePlainTextResponse($request);
        }

        return $this->generateTemplatedResponse($this->renderer, $request);
    }

    /**
     * Generates a plain text response indicating the request method and URI.
     */
    private function generatePlainTextResponse(ServerRequestInterface $request) : ResponseInterface
    {
        $response = ($this->responseFactory)()->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        $response->getBody()
            ->write(sprintf(
                'Cannot %s %s',
                $request->getMethod(),
                (string) $request->getUri()
            ));

        return $response;
    }

    /**
     * Generates a response using a template.
     *
     * Template will receive the current request via the "request" variable.
     */
    private function generateTemplatedResponse(
        TemplateRendererInterface $renderer,
        ServerRequestInterface $request
    ) : ResponseInterface {

        $baseUrl = $request->getAttribute(InjectBaseUrlMiddleware::class);
        $response = ($this->responseFactory)()->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        $vars = ['baseUrl' => $baseUrl, 'request' => $request, 'layout' => $this->layout];
        print_r($vars);
        die;
        $response->getBody()->write(
            $renderer->render($this->template, $vars)
        );

        return $response;
    }
}
