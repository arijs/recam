<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
// use Zend\Expressive\Router\RouterInterface;
use Zend\Validator\EmailAddress;
use Zend\Authentication\AuthenticationService;
use \App\MyAuthAdapter;
use \App\Model\Usuario;
use \App\Model\UsuarioTable;
use \App\Model\LocalReuniao;
use \App\Model\LocalReuniaoTable;

class UsuarioLocalReuniaoHandler implements RequestHandlerInterface
{
    // private $router;
    private $template;
    private $usuarioTable;
    private $localReuniaoTable;
    private $auth;
    private $authAdapter;

    public function __construct(
        // RouterInterface $router,
        TemplateRendererInterface $template,
        UsuarioTable $usuarioTable,
        LocalReuniaoTable $localReuniaoTable,
        AuthenticationService $auth,
        MyAuthAdapter $authAdapter
    ) {
        // $this->router       = $router;
        $this->template     = $template;
        $this->usuarioTable = $usuarioTable;
        $this->localReuniaoTable = $localReuniaoTable;
        $this->auth         = $auth;
        $this->authAdapter  = $authAdapter;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            return $this->handlePost($request);
        }

        return new JsonResponse([
            'baseUrl' => $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class),
            'error' => 'Method not allowed: '.$request->getMethod(),
        ], 405);
    }

    public function handlePost(ServerRequestInterface $request) : ResponseInterface
    {
        $params = $request->getParsedBody();
        $baseUrl = $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class);
        $session = $request->getAttribute(\App\Middleware\InjectAuthMiddleware::class);

        $usuario_id = isset($params['usuario_id']) ? $params['usuario_id'] : null;
        $reuniao_id = isset($params['reuniao_id']) ? $params['reuniao_id'] : null;
        $geo_id = isset($params['geo_id']) ? $params['geo_id'] : null;

        $erro_usuario = [];
        $erro_localReuniao = [];
        $update_success = false;

        if ($usuario_id != $session['usuario']['usuario_id']) {
            $erro_usuario[] = 'ID do usuário inválido!';
        }

        if (empty($reuniao_id) || !is_numeric($reuniao_id)) {
            $erro_localReuniao[] = 'ID do local de reunião inválido!';
        }

        if (empty($geo_id)) {
            $erro_localReuniao[] = 'Código geográfico do local de reunião não informado!';
        }

        if (empty($erro_usuario) && empty($erro_localReuniao)) {
            $lr = $this->localReuniaoTable->getLocalReuniao($reuniao_id);
            if (empty($lr)) {
                $erro_localReuniao[] = 'Local de reunião não encontrado!';
            } else if ($lr->geo_id !== $geo_id) {
                $erro_localReuniao[] = 'Código geográfico do local de reunião inválido!';
            } else {
                $this->usuarioTable->updateUsuarioArray($usuario_id, [
                    'id_reuniao' => $reuniao_id,
                ]);
                $update_success = true;
            }
        }

        $errors = [];
        if (!empty($erro_usuario)) $errors['usuario'] = $erro_usuario;
        if (!empty($erro_localReuniao)) $errors['localReuniao'] = $erro_localReuniao;

        return new JsonResponse([
            'baseUrl' => $baseUrl,
            'success' => $update_success,
            'error' => !$update_success,
            'errorFields' => empty($errors) ? null : $errors,
        ], $update_success ? 200 : (empty($errors) ? 500 : 400));
    }
}
