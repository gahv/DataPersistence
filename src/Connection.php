<?php

namespace gahv\DataPersistence;

use PDO;
use PDOException;

/**
 * Class Connection
 * @package gahv\DataPersistence
 */
class Connection
{
    /** @var PDO */
    private static $instance;

    /** @var string */
    private static $connection_string;

    /** @var PDOException */
    private static $error;

    /** @var string */
    private static $db_user;

    /** @var string */
    private static $db_password;

    /** @var string */
    private static $db_name;

    /**
     * @return PDO
     */
    public static function getInstance(?string $db_user = "", ?string $db_password = "", ?string $db_name = ""): ?PDO
    {
        self::$db_user     = ($db_user) ? $db_user : DB_CONFIG["username"];
        self::$db_password = ($db_password) ? $db_password : DB_CONFIG["passwd"];
        self::$db_name     = ($db_name) ? $db_name : DB_CONFIG["dbname"];

        $conn_string = self::getConnectionString(self::$db_name);

        // Check connection string changes
        if (self::$connection_string != $conn_string) {
            try {
                self::$instance = new PDO(
                    $conn_string,
                    self::$db_user,
                    self::$db_password,
                    DB_CONFIG["options"]
                );
            } catch (PDOException $exception) {
                self::$error = $exception;
            }
        }

        if (empty(self::$instance)) {
            try {
                self::$instance = new PDO(
                    $conn_string,
                    self::$db_user,
                    self::$db_password,
                    DB_CONFIG["options"]
                );
            } catch (PDOException $exception) {
                self::$error = $exception;
            }
        }

        return self::$instance;
    }


    /**
     * @return PDOException|null
     */
    public static function getError(): ?PDOException
    {
        return self::$error;
    }

    /**
     * Connection constructor.
     */
    final private function __construct()
    {
    }

    /**
     * Connection clone.
     */
    final private function __clone()
    {
    }

    /**
     * Dynamic Connection String
     */
    private static function getConnectionString(string $db_name): string
    {
        switch (DB_CONFIG["driver"]) {
            case 'sqlsrv':
                self::$connection_string =
                    DB_CONFIG["driver"] . ":Server=" . DB_CONFIG["host"] . "," . DB_CONFIG["port"] . ";Database=" . $db_name . ";WSID=" . DB_CONFIG["appname"] . ";";
                break;
            default:
                self::$connection_string =
                    DB_CONFIG["driver"] . ":host=" . DB_CONFIG["host"] . ";dbname=" . $db_name . ";port=" . DB_CONFIG["port"] . " ";
        }

        return self::$connection_string;
    }
}