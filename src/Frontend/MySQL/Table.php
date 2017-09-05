<?php

declare(strict_types=1);

namespace Hywan\DatabaseToPlantUML\Frontend\MySQL;

use Hoa\Database\DalStatement;
use Hywan\DatabaseToPlantUML\Frontend;
use PDO;

class Table extends Frontend\Table
{
    public function columns(): iterable
    {
        $columns =
            $this->getDatabaseConnection()
                ->prepare(
                    'SELECT    c.column_name AS name, ' .
                    '          c.column_default AS defaultValue, ' .
                    '          CASE c.is_nullable WHEN "YES" THEN 1 ELSE 0 END AS isNullable, ' .
                    '          c.column_type AS type, ' .
                    '          k.constraint_name AS constraintName, ' .
                    '          k.referenced_table_name AS referencedTableName, ' .
                    '          k.referenced_column_name AS referencedColumnName ' .
                    'FROM      information_schema.columns AS c ' .
                    'LEFT JOIN information_schema.key_column_usage AS k ' .
                    'ON        k.table_schema = c.table_schema ' .
                    'AND       k.table_name   = c.table_name ' .
                    'AND       k.column_name  = c.column_name ' .
                    'WHERE     c.table_schema = :database_name ' .
                    'AND       c.table_name = :table_name ' .
                    'ORDER BY  c.ordinal_position ASC',
                    [
                        PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL
                    ]
                )
                ->execute([
                    'database_name' => $this->databaseName,
                    'table_name'    => $this->name
                ]);

        $columns->setFetchingStyle(
            DalStatement::FROM_START,
            DalStatement::FORWARD,
            DalStatement::AS_CLASS,
            Column::class
        );

        yield from $columns;
    }
}
