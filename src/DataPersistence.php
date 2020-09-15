<?php

namespace gahv\DataPersistence;

use Exception;
use PDO;
use PDOException;
use stdClass;

/**
 * Class DataPersistence
 * @package gahv\DataPersistence
 */
abstract class DataPersistence
{
    use CrudTrait;

    /** @var string $entity database table */
    private $entity;

    /** @var string $id_autoincrement table auto increment (identity) field */
    private $id_autoincrement;

    /** @var array $required table required fields */
    private $required;

    /** @var string $timestamps control created and updated at */
    private $timestamps;

    /** @var string */
    protected $statement;

    /** @var string */
    protected $params;

    /** @var string */
    protected $group;

    /** @var string */
    protected $order;

    /** @var int */
    protected $limit;

    /** @var int */
    protected $offset;

    /** @var \PDOException|null */
    protected $fail;

    /** @var object|null */
    protected $data;

    /** @var string */
    protected $db_user;

    /** @var string */
    protected $db_password;

    /** @var string */
    protected $db_name;

    /**
     * DataPersistence constructor.
     * @param string $entity
     * @param array $required
     * @param string $id_autoincrement
     * @param bool $timestamps
     */
    public function __construct(string $entity, array $required, string $id_autoincrement = 'id', bool $timestamps = false)
    {
        $this->entity = $entity;
        $this->id_autoincrement = $id_autoincrement;
        $this->required = $required;
        $this->timestamps = $timestamps;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setConnInfo($user, $pwd, $db)
    {
        $this->db_user = $user;
        $this->db_password = $pwd;
        $this->db_name = $db;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (empty($this->data)) {
            $this->data = new stdClass();
        }

        $this->data->$name = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data->$name);
    }

    /**
     * @param $name
     * @return string|null
     */
    public function __get($name)
    {
        $method = $this->toCamelCase($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        if (method_exists($this, $name)) {
            return $this->$name();
        }

        return ($this->data->$name ?? null);
    }

    /**
     * @return object|null
     */
    public function data(): ?object
    {
        return $this->data;
    }

    /**
     * @return PDOException|Exception|null
     */
    public function fail()
    {
        return $this->fail;
    }

    /**
     * @param string|null $terms
     * @param string|null $params
     * @param string $columns
     * @return DataPersistence
     */
    public function find(?string $terms = null, ?string $params = null, string $columns = "*"): DataPersistence
    {
        if ($terms) {
            $this->statement = "SELECT {$columns} FROM {$this->entity} WHERE {$terms}";
            parse_str($params, $this->params);
            return $this;
        }

        $this->statement = "SELECT {$columns} FROM {$this->entity}";
        return $this;
    }

    /**
     * @param int $id
     * @param string $columns
     * @return DataPersistence|null
     */
    public function findById(int $id, string $columns = "*"): ?DataPersistence
    {
        return $this->find("{$this->id_autoincrement} = :id", "id={$id}", $columns)->fetch();
    }

    /**
     * @param string $column
     * @return DataPersistence|null
     */
    public function group(string $column): ?DataPersistence
    {
        $this->group = " GROUP BY {$column}";
        return $this;
    }

    /**
     * @param string $columnOrder
     * @return DataPersistence|null
     */
    public function order(string $columnOrder): ?DataPersistence
    {
        $this->order = " ORDER BY {$columnOrder}";
        return $this;
    }

    /**
     * @param int $limit
     * @return DataPersistence|null
     */
    public function limit(int $limit): ?DataPersistence
    {
        $this->limit = " LIMIT {$limit}";
        return $this;
    }

    /**
     * @param int $offset
     * @return DataPersistence|null
     */
    public function offset(int $offset): ?DataPersistence
    {
        $this->offset = " OFFSET {$offset}";
        return $this;
    }

    /**
     * @param bool $all
     * @return array|mixed|null
     */
    public function fetch(bool $all = false)
    {
        try {
            $stmt = Connection::getInstance($this->db_user, $this->db_password, $this->db_name);
            $stmt = $stmt->prepare($this->statement . $this->group . $this->order . $this->limit . $this->offset);
            $stmt->execute($this->params);

            if (!$stmt->rowCount()) {
                return null;
            }

            if ($all) {
                return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
            }

            return $stmt->fetchObject(static::class);
        } catch (PDOException $exception) {
            $this->fail = $exception;
            return null;
        }
    }

    /**
     * @return int
     */
    public function count(): int
    {
        $stmt = Connection::getInstance($this->db_user, $this->db_password, $this->db_name);
        $stmt = $stmt->prepare($this->statement);
        $stmt->execute($this->params);
        return $stmt->rowCount();
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        $id_autoincrement = $this->id_autoincrement;
        $id = null;

        try {
            if (!$this->required()) {
                throw new Exception("Preencha os campos necessários");
            }

            /** Update */
            if (!empty($this->data->$id_autoincrement)) {
                $id = $this->data->$id_autoincrement;
                $this->update($this->safe(), "{$this->id_autoincrement} = :id", "id={$id}");
            }

            /** Create */
            if (empty($this->data->$id_autoincrement)) {
                $id = $this->create($this->safe());
            }

            if (!$id) {
                return false;
            }

            $this->data = $this->findById($id)->data();
            return true;
        } catch (Exception $exception) {
            $this->fail = $exception;
            return false;
        }
    }

    /**
     * @return bool
     */
    public function destroy(): bool
    {
        $id_autoincrement = $this->id_autoincrement;
        $id = $this->data->$id_autoincrement;

        if (empty($id)) {
            return false;
        }

        return $this->delete("{$this->id_autoincrement} = :id", "id={$id}");
    }

    /**
     * @return bool
     */
    protected function required(): bool
    {
        $data = (array)$this->data();
        foreach ($this->required as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array|null
     */
    protected function safe(): ?array
    {
        $safe = (array)$this->data;
        unset($safe[$this->id_autoincrement]);
        return $safe;
    }


    /**
     * @param string $string
     * @return string
     */
    protected function toCamelCase(string $string): string
    {
        $camelCase = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        $camelCase[0] = strtolower($camelCase[0]);
        return $camelCase;
    }
}
