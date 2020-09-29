<?php

require "db_config.php";
require "../vendor/autoload.php";

use gahv\DataPersistence\DataPersistence;

class Empresa extends DataPersistence
{
    public function __construct()
    {
        parent::__construct("empresas", ["cnpj, razao_social"]);
    }
}

$dados = new Empresa();

// Chage connection string in runtime
$dados->setConnInfo("new_user", "new_pwd", "other_or_same_database");

// Returns first record
$dados = $dados->find("", "", "cnpj, razao_social")->fetch();

// Returns all records
$dados = $dados->find("", "", "cnpj, razao_social")->fetch(true);

// Returns the 5 first records
$dados = $dados->top(5)->find("", "", "cnpj, razao_social")->order("razao_social")->fetch(true);

// Returns record by param
$params = http_build_query(["cnpj" => "001"]);
$dados = $dados->find("cnpj = :cnpj", $params, "cnpj, razao_social")->fetch(true);

var_dump($dados);