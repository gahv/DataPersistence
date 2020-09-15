<?php

require "db_config.php";
require "../vendor/autoload.php";

use gahv\DataPersistence\Connection;

/*
 * PDO
 */

// Get connection with DB_CONFIG info
$connection = Connection::getInstance();

// Get connection with dynamic info
$connection = Connection::getInstance("user", "password", "database");

// Get PDOException object
$error = Connection::getError();

if ($error) {
    echo $error->getMessage();
    echo Connection::getErrorMessage();
    exit;
}

$users = $connection->query("SELECT TOP 1 * FROM users ");
var_dump($users->fetchAll());
