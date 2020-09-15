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
        $currentConn = hash("md5", self::$db_user . self::$db_password . self::$db_name);
        $customConn = hash("md5", $db_user . $db_password . $db_name);

        // Check changes in connection information
        $newinstance = ($currentConn != $customConn);

        self::$db_user     = ($db_user) ? $db_user : DB_CONFIG["username"];
        self::$db_password = ($db_password) ? $db_password : DB_CONFIG["passwd"];
        self::$db_name     = ($db_name) ? $db_name : DB_CONFIG["dbname"];

        $conn_string = self::getConnectionString(self::$db_name);

        if ($newinstance) {
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
                    DB_CONFIG["driver"] . ":Server=" . DB_CONFIG["host"] . "," . DB_CONFIG["port"] . ";Database=" . $db_name . ";app=" . DB_CONFIG["appname"] . ";";
                break;
            default:
                self::$connection_string =
                    DB_CONFIG["driver"] . ":host=" . DB_CONFIG["host"] . ";dbname=" . $db_name . ";port=" . DB_CONFIG["port"] . " ";
        }

        return self::$connection_string;
    }

    /**
     * Formats error message
     */
    public static function getErrorMessage(): string
    {
        $error = "";

        switch (DB_CONFIG["driver"]) {
            case 'sqlsrv':
                $error = self::$error->getMessage();
                $error = substr($error, strripos($error, '[SQL Server]') + 12);
                break;
            default:
                $error = self::$error->getMessage();
        }

        return $error;
    }
}