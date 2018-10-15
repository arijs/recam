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

class UsuarioCadastrarHandler implements RequestHandlerInterface
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
            return $this->authenticate($request);
        }

        return new JsonResponse([
            'baseUrl' => $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class),
            'error' => 'Method not allowed: '.$request->getMethod(),
        ], 405);
    }

    public function validateRede($session, $name, $params, &$login_redes, &$id_redes, &$error_redes)
    {
        $value = isset($params[$name]) ? $params[$name] : null;
        $valid = false;
        if (!empty($value)) {
            $value = explode(' ', $value);
            $rede = isset($session[$name]) ? $session[$name] : null;
            $rede = isset($rede, $rede['user']) ? $rede['user'] : null;
            if (empty($rede)) {
                $error_redes[] = "Login {$name} não encontrado, faça o login novamente";
            } else {
                $id_name = 'id_'.$name;
                $id_row = isset($rede['id'], $value[0]) ? $rede['id'] == $value[0] : false;
                $id_rede = isset($rede[$id_name], $value[1]) ? $rede[$id_name] == $value[1] : false;
                $valid = $id_row && $id_rede;
                if (!$id_row) {
                    $error_redes[] = "Login {$name}: id banco inválido";
                }
                if (!$id_rede) {
                    $error_redes[] = "Login {$name}: id rede inválido";
                }
                if ($valid) {
                    $login_redes[] = $rede;
                    $id_redes[$id_name] = $rede['id'];
                }
            }
        }
        return $valid;
    }

    public function authenticate(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();
        $baseUrl = $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class);
        $session = $request->getAttribute(\App\Middleware\InjectAuthMiddleware::class);

        $name = isset($params['name']) ? $params['name'] : null;
        $email = isset($params['email']) ? $params['email'] : null;
        $password = isset($params['password']) ? $params['password'] : null;
        $password_confirm = isset($params['password_confirm']) ? $params['password_confirm'] : null;

        $redes_name = ['facebook', 'google', 'twitter', 'linkedin'];

        // $facebook = isset($params['facebook']) ? $params['facebook'] : null;
        // $google = isset($params['google']) ? $params['google'] : null;
        // $twitter = isset($params['twitter']) ? $params['twitter'] : null;
        // $linkedin = isset($params['linkedin']) ? $params['linkedin'] : null;

        $login_redes = [];
        $id_redes = [];
        $error_redes = [];

        $error_name = [];
        $error_email = [];
        $error_password = [];
        $error_password_confirm = [];
        $register_success = false;
        $emailValidator = new EmailAddress();

        foreach ($redes_name as $rede_name) {
            $this->validateRede($session, $rede_name, $params, $login_redes, $id_redes, $error_redes);
        }

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

        if (empty($login_redes)) {
            if (empty($password)) {
                $error_password[] = 'A senha está vazia!';
            }

            if (empty($password_confirm)) {
                $error_password_confirm[] = 'É necessário confirmar a senha!';
            }

            else if ($password_confirm !== $password) {
                $error_password_confirm[] = 'A senha e a confirmação da senha são diferentes!';
            }
        }

        $errors = [];
        if (!empty($error_name)) $errors['name'] = $error_name;
        if (!empty($error_email)) $errors['email'] = $error_email;
        if (!empty($error_password)) $errors['password'] = $error_password;
        if (!empty($error_password_confirm)) $errors['password_confirm'] = $error_password_confirm;
        if (!empty($error_redes)) $errors['redes'] = $error_redes;

        if (empty($errors)) {
            $usuario = new Usuario();
            $usuario->irmao_id = 0;
            $usuario->usuario_nome = $name;
            $usuario->usuario_email = $email;
            $usuario->usuario_senha = $password;
            foreach ($id_redes as $rede_col => $rede_id) {
                $usuario->{$rede_col} = $rede_id;
            }
            $this->usuarioTable->insertUsuario($usuario);

            if ($this->auth->hasIdentity()) {
                $this->authAdapter->setCurrentIdentity($this->auth->getIdentity());
            }
            $this->authAdapter->setUsuario($usuario);
            $result = $this->auth->authenticate();
            if ($result->isValid()) {
                $session = $result->getIdentity();
                $register_success = true;
            } else {
                $errors = array_merge($errors, $result->getMessages());
                $register_success = false;
            }
        }

        return new JsonResponse([
            'baseUrl' => $request->getAttribute(\App\Middleware\InjectBaseUrlMiddleware::class),
            'error' => !$register_success,
            'errorFields' => empty($errors) ? null : $errors,
            'register_name' => $name,
            'register_email' => $email,
            // 'register_password' => $password,
            // 'usuario' => $usuario,
            'session' => $session,
            // 'register_' => $,
        ], $register_success ? 200 : (empty($errors) ? 500 : 400));
    }
}
