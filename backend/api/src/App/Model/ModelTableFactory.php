<?php

namespace App\Model;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;

class ModelTableFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $tableClass, array $options = null)
    {
        $config = $container->get('config');
        $modelConfig = $config['models']['mapTable'][$tableClass];
        $modelClass = $modelConfig['modelClass'];
        $tableName = $modelConfig['tableName'];
        $dbAdapter = $container->get(AdapterInterface::class);
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new $modelClass());
        $tableGateway = new TableGateway($tableName, $dbAdapter, null, $resultSetPrototype);
        return new $tableClass($tableGateway);
    }
}
