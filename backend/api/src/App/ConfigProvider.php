<?php

declare(strict_types=1);

namespace App;

use Zend\Authentication\AuthenticationService;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'invokables' => [
                Handler\PingHandler::class => Handler\PingHandler::class,
            ],
            'factories'  => [
                AuthenticationService::class => AuthenticationServiceFactory::class,
                MyAuthAdapter::class => MyAuthAdapterFactory::class,
                Middleware\InjectBaseUrlMiddleware::class => Middleware\InjectBaseUrlMiddlewareFactory::class,
                Middleware\InjectAuthMiddleware::class => Middleware\InjectAuthMiddlewareFactory::class,
                Middleware\CheckAuthMiddleware::class => Middleware\CheckAuthMiddlewareFactory::class,
                Handler\HomePageHandler::class => Handler\HomePageHandlerFactory::class,
                Handler\LoginHandler::class => Handler\LoginHandlerFactory::class,
                Handler\LogoutHandler::class => Handler\LogoutHandlerFactory::class,
                Handler\UsuarioCadastrarHandler::class => Handler\UsuarioHandlerFactory::class,
                Handler\UsuarioLocalReuniaoHandler::class => Handler\UsuarioHandlerFactory::class,
                Handler\WeeklyMeetings::class => Handler\WeeklyMeetingsFactory::class,
                Handler\WeeklyMeetingsDb::class => Handler\WeeklyMeetingsFactory::class,
                Handler\MailHandler::class => Handler\MailHandlerFactory::class,
                Model\UsuarioTable::class => Model\ModelTableFactory::class,
                Model\UsuarioAcessoTable::class => Model\ModelTableFactory::class,
                Model\UsuarioFacebookTable::class => Model\ModelTableFactory::class,
                Model\UsuarioGoogleTable::class => Model\ModelTableFactory::class,
                Model\UsuarioTwitterTable::class => Model\ModelTableFactory::class,
                Model\UsuarioLinkedinTable::class => Model\ModelTableFactory::class,
                Model\LocalReuniaoTable::class => Model\ModelTableFactory::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
    public function getTemplates() : array
    {
        return [
            'paths' => [
                'app'    => ['templates/app'],
                'error'  => ['templates/error'],
                'layout' => ['templates/layout'],
            ],
        ];
    }
}
