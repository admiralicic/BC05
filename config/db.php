<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=cdrs_db',
    'username' => 'root',
    'password' => 'beemerdude',
    'charset' => 'utf8',
    'attributes' => array(PDO::MYSQL_ATTR_LOCAL_INFILE => true),
];
