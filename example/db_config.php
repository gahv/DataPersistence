<?php

define("DB_CONFIG", [
    "driver" => "sqlsrv",
    "host" => "localhost",
    "port" => "1433",
    "dbname" => "database",
    "username" => "user",
    "passwd" => "password",
    "appname" => "DataPersistence",
    "options" => [
        //PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ]
]);