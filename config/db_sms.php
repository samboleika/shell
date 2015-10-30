<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=shell-dev',
    'username' => 'postgres',
    'password' => 'postgres',
    'charset' => 'utf8',
    'schemaMap' => [
      'pgsql'=> [
        'class'=>'yii\db\pgsql\Schema',
        'defaultSchema' => 'sms' //specify your schema here
      ]
    ], // PostgreSQL
];
