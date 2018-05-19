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
use \App\Model\Usuario;
use \App\Model\UsuarioTable;

class UsuarioCadastrarHandler implements RequestHandlerInterface
{
    // private $router;
    private $template;
    private $usuarioTable;

    public function __construct(
        // RouterInterface $router,
        TemplateRendererInterface $template,
        UsuarioTable $usuarioTable
    ) {
        // $this->router       = $router;
        $this->template     = $template;
        $this->usuarioTable = $usuarioTable;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            return $this->authenticate($request);
        }

        return new JsonResponse([
            'baseUrl' => $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class),
            'error' => 'Method not allowed: '.$request->getMethod(),
        ], 405);
    }

    public function authenticate(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();
        $baseUrl = $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class);

        $name = isset($params['name']) ? $params['name'] : null;
        $email = isset($params['email']) ? $params['email'] : null;
        $password = isset($params['password']) ? $params['password'] : null;
        $password_confirm = isset($params['password_confirm']) ? $params['password_confirm'] : null;

        $error_name = [];
        $error_email = [];
        $error_password = [];
        $error_password_confirm = [];
        $register_success = false;
        $emailValidator = new EmailAddress();

        if (empty($name)) {
            // return new HtmlResponse($this->template->render('app::login', [
            //     'error' => 'The username cannot be empty',
            // ]));
            $error_name[] = 'O nome está vazio!';
        }

        if (empty($email)) {
            $error_email[] = 'O e-mail está vazio!';
        }

        else if (!$emailValidator->isValid($email)) {
            $error_email[] = 'O endereço de e-mail é inválido!';
        }

        else if ($userEmail = $this->usuarioTable->getUsuarioByEmail($email)) {
            $error_email[] = 'Já existe um usuário com esse e-mail! ('.$userEmail->usuario_nome.')';
        }

        if (empty($password)) {
            $error_password[] = 'A senha está vazia!';
        }

        if (empty($password_confirm)) {
            $error_password_confirm[] = 'É necessário confirmar a senha!';
        }

        else if ($password_confirm !== $password) {
            $error_password_confirm[] = 'A senha e a confirmação da senha são diferentes!';
        }

        $errors = [];
        if (!empty($error_name)) $errors['name'] = $error_name;
        if (!empty($error_email)) $errors['email'] = $error_email;
        if (!empty($error_password)) $errors['password'] = $error_password;
        if (!empty($error_password_confirm)) $errors['password_confirm'] = $error_password_confirm;

        if (empty($errors)) {
            $usuario = new Usuario();
            $usuario->irmao_id = 0;
            $usuario->usuario_nome = $name;
            $usuario->usuario_email = $email;
            $usuario->usuario_senha = $password;
            $this->usuarioTable->insertUsuario($usuario);
            $register_success = true;
        }

        return new JsonResponse([
            'baseUrl' => $request->getAttribute(\App\Middleware\InjectAuthMiddleware::class),
            'error' => !$register_success,
            'errorFields' => empty($errors) ? null : $errors,
            'register_name' => $name,
            'register_email' => $email,
            // 'register_password' => $password,
            'usuario' => $usuario,
            // 'register_' => $,
        ], $register_success ? 200 : (empty($errors) ? 500 : 400));
    }
}
