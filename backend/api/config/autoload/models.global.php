<?php

$modelList = [
    [
        'modelClass' => \App\Model\Usuario::class,
        'tableClass' => \App\Model\UsuarioTable::class,
        'tableName' => 'usuarios',
    ],
    [
        'modelClass' => \App\Model\UsuarioAcesso::class,
        'tableClass' => \App\Model\UsuarioAcessoTable::class,
        'tableName' => 'usuarios_acessos',
    ],
];
$byModel = [];
$byTable = [];

foreach ( $modelList as $model ) {
    $modelClass = $model['modelClass'];
    $tableClass = $model['tableClass'];
    $byModel[$modelClass] = $model;
    $byTable[$tableClass] = $model;
}

return [
    'models' => [
        'list' => $modelList,
        'mapModel' => $byModel,
        'mapTable' => $byTable,
    ],
];
