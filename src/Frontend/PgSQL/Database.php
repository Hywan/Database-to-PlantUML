<?php

declare(strict_types=1);

namespace Hywan\DatabaseToPlantUML\Frontend\PgSQL;

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
                    'SELECT table_catalog AS databaseName, ' .
                    '       table_name AS name ' .
                    'FROM   information_schema.tables ' .
                    'WHERE  table_catalog = :database_name ' .
                    'AND    table_schema = :table_schema',
                    [
                        PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL
                    ]
                )
                ->execute([
                    'database_name' => $this->name,
                    'table_schema'  => 'public'
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
