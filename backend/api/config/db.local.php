<?php

return [
    'db' => [
        // 'driver' => 'Pdo',
        // 'dsn'    => 'mysql:dbname=databasename;host=localhost;charset=utf8',
        // 'username' => 'databaseuser',
        // 'password' => 'databasepass',
        'driver'   => 'Mysqli',
        //'hostname' => 'www.registrodecampo.com.br',
        'hostname' => 'localhost',
        'port' => 3306,
        'charset' => 'utf8',
        'database' => 'registrodecampo_db',
        'username' => 'registrodecampo_backend',
        'password' => 'rdc2018##be',
    ],
];
