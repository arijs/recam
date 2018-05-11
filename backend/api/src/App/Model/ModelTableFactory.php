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
        // $modelClass = $tableClass::$model;
        // $tableName = $tableClass::$tableName;
        $dbAdapter = $container->get(AdapterInterface::class);
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new $tableClass::$model());
        $tableGateway = new TableGateway($tableClass::$tableName, $dbAdapter, null, $resultSetPrototype);
        return new $tableClass($tableGateway);
    }
}
