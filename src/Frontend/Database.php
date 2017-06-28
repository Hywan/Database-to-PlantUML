<?php

declare(strict_types=1);

namespace Hywan\MySQLToPlantUML\Frontend;

use Hoa\Database\Dal;
use Hoa\Database\DalStatement;
use PDO;

class Database
{
    protected $_databaseConnection = null;
    public $name;

    public function __construct(Dal $databaseConnection, string $name)
    {
        $this->_databaseConnection = $databaseConnection;
        $this->name                = $name;
    }

    public function tables()
    {
        $tables =
            $this->_databaseConnection
                ->prepare(
                    'SELECT table_schema AS databaseName, ' .
                    '       table_name AS name, ' .
                    '       engine, ' .
                    '       table_comment AS comment ' .
                    'FROM   information_schema.tables ' .
                    'WHERE  table_schema = :database_name',
                    [
                        PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL
                    ]
                )
                ->execute([
                    'database_name' => $this->name
                ]);

        $tables->setFetchingStyle(
            DalStatement::FROM_START,
            DalStatement::FORWARD,
            DalStatement::AS_CLASS,
            Table::class,
            [
                $this->_databaseConnection
            ]
        );

        yield from $tables;
    }
}
