<?php

declare(strict_types=1);

namespace Hywan\DatabaseToPlantUML\Frontend;

use Hoa\Database\Dal;
use Hoa\Database\DalStatement;
use PDO;

abstract class Reader
{
    protected $_databaseConnection = null;

    public function __construct(string $dsn, string $user, string $password)
    {
        $this->_databaseConnection = Dal::getInstance(
            'main',
            Dal::PDO,
            $dsn,
            $user,
            $password
        );
    }

    abstract public function read(string $databaseName): Database;

    public function getDatabaseConnection(): Dal
    {
        return $this->_databaseConnection;
    }
}
