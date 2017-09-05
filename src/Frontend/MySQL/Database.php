<?php

declare(strict_types=1);

namespace Hywan\DatabaseToPlantUML\Frontend\MySQL;

use Hoa\Database\DalStatement;
use Hywan\DatabaseToPlantUML\Frontend;
use PDO;

class Database extends Frontend\Database
{
    public function tables(): iterable
    {
        $tables =
            $this->getDatabaseConnection()
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
                $this->getDatabaseConnection()
            ]
        );

        yield from $tables;
    }
}
