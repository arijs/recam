<?php
// In src/Auth/src/MyAuthAdapter.php:

namespace App;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Abraham\TwitterOAuth\TwitterOAuth;

class MyAuthAdapter implements AdapterInterface
{
    private $account;
    private $currentIdentity;
    private $facebook;
    private $google;
    private $twitter;
    private $usuarioTable;
    private $usuarioAcessoTable;
    private $config;

    public function __construct(
        Model\UsuarioTable $usuarioTable,
        Model\UsuarioAcessoTable $usuarioAcessoTable,
        array $config
    ) {
        $this->usuarioTable = $usuarioTable;
        $this->usuarioAcessoTable = $usuarioAcessoTable;
        $this->config = $config;
    }

    public function setAccount(string $username, string $password) : void
    {
        $this->account = array(
            'username' => $username,
            'password' => $password,
        );
    }

    public function setCurrentIdentity($identity) : void
    {
        $this->currentIdentity = $identity;
    }

    public function setFacebook($facebook) : void
    {
        $this->facebook = $facebook;
    }

    public function setGoogle($google) : void
    {
        $this->google = $google;
    }

    public function setTwitter($twitter) : void
    {
        $this->twitter = $twitter;
    }

    /**
     * Performs an authentication attempt
     *
     * @return Result
     */
    public function authenticate()
    {
        // Retrieve the user's information (e.g. from a database)
        // and store the result in $row (e.g. associative array).
        // If you do something like this, always store the passwords using the
        // PHP password_hash() function!
        if (empty($this->currentIdentity)) {
            $success = false;
            $identity = [];
        } else {
            $success = true;
            $identity = $this->currentIdentity;
        }

        if (!empty($this->account)) {
            $identity['username'] = null;
            $identity['usuario'] = null;
            $identity['acesso'] = null;
            $u = $this->account['username'];
            $p = $this->account['password'];

            $usuario = $this->usuarioTable->getUsuarioByEmail($u);
            $usuarioMatch = empty($usuario) ? false : $usuario->usuario_senha === $p;
            if ($usuarioMatch) {
                // $uaut = $usuario->usuario_autorizado;
                /*if (empty($uaut)) {
                    return new Result(Result::FAILURE, $u, [
                        'Sua conta ainda não foi autorizada pelo administrador!',
                    ]);
                }*/
                $acesso = $this->usuarioAcessoTable->getAcessoDeHoje($usuario->usuario_id);
                if (empty($acesso)) {
                    $acesso = $this->usuarioAcessoTable->criarAcessoDeHoje($usuario->usuario_id);
                } else {
                    $acesso = $this->usuarioAcessoTable->updateAcessoDeHoje($acesso);
                }
                $identity['username'] = $u;
                $identity['usuario'] = $usuario;
                $identity['acesso'] = $acesso;
                $success = true;
            } else {
                return new Result(Result::FAILURE_CREDENTIAL_INVALID, $u, [
                    'Login ou senha inválidos'
                ]);
            }

        }

        if (!empty($this->facebook)) {
            $identity['facebook'] = $this->facebook;
            $success = true;
        }

        if (!empty($this->google)) {
            $identity['google'] = $this->google;
            $success = true;
        }

        if (!empty($this->twitter)) {
            $identity['twitter'] = $this->twitter;
            $success = true;
        }

        if ($success) {
            return new Result(Result::SUCCESS, $identity);
        } else {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, [], [
                'Nenhuma credencial informada'
            ]);
        }
    }

    public function getFacebookProvider($returnUrl)
    {
        $config = $this->config['facebook'];
        return new \League\OAuth2\Client\Provider\Facebook([
            'clientId'          => $config['app_id'],
            'clientSecret'      => $config['app_secret'],
            'redirectUri'       => $returnUrl,
            'graphApiVersion'   => 'v3.1',
        ]);
    }

    public function initFacebook($returnUrl)
    {
        $provider = $this->getFacebookProvider($returnUrl);
        $authUrl = $provider->getAuthorizationUrl();
        return [
            'provider' => $provider,
            'auth_url' => $authUrl,
            'state' => $provider->getState(),
        ];
    }

    public function getGoogleProvider($returnUrl)
    {
        $config = $this->config['google'];
        return new \League\OAuth2\Client\Provider\Google([
            'clientId'          => $config['app_id'],
            'clientSecret'      => $config['app_secret'],
            'redirectUri'       => $returnUrl,
        ]);
    }

    public function initGoogle($returnUrl)
    {
        $provider = $this->getGoogleProvider($returnUrl);
        $authUrl = $provider->getAuthorizationUrl();
        return [
            'provider' => $provider,
            'auth_url' => $authUrl,
            'state' => $provider->getState(),
        ];
    }

    public function getTwitterProvider()
    {
        $config = $this->config['twitter'];
        return new TwitterOAuth($config['app_id'], $config['app_secret'], $config['access_token'], $config['access_token_secret']);
    }

    public function initTwitter($returnUrl)
    {
        $provider = $this->getTwitterProvider();
        $requestToken = $provider->oauth('oauth/request_token', [
            'oauth_callback' => $returnUrl,
        ]);
        $oauthToken = $requestToken['oauth_token'];
        $authUrl = $provider->url("oauth/authenticate", ["oauth_token" => $oauthToken]);
        return [
            'provider' => $provider,
            // 'request_token' => $requestToken,
            'auth_url' => $authUrl,
            'oauth_token' => $oauthToken,
            'oauth_token_secret' => $requestToken['oauth_token_secret'],
            'oauth_callback_confirmed' => $requestToken['oauth_callback_confirmed']
        ];
    }
}
