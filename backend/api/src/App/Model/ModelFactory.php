<?php

namespace App\Model;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ModelFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $modelName, array $options = null)
    {
        $config = $container->get('config');
        $tableClass = $config['models']['mapModel'][$modelName]['tableClass'];
        $table = $container->get($tableClass);
        return new $modelName($table);
    }
}
