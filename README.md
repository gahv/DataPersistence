# Data Persistence

###### Baseado no componente [Data Layer @CoffeeCode](https://github.com/robsonvleite/datalayer)

## Documentação

#### Connection

Para criar a conexão padrão com o banco dedados é necessário informar os parâmetros da constante DB_CONFIG

```php
define("DB_CONFIG", [
    "driver" => "mysql",
    "host" => "localhost",
    "port" => "3306",
    "dbname" => "database",
    "username" => "user",
    "passwd" => "password",
    "appname" => "DataPersistence",
    "options" => [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ]
]);
```