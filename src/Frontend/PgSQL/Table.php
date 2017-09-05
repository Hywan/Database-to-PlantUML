<?php

declare(strict_types=1);

namespace Hywan\DatabaseToPlantUML\Frontend\PgSQL;

use Hoa\Database\DalStatement;
use Hywan\DatabaseToPlantUML\Frontend;
use PDO;

class Table extends Frontend\Table
{
    const PRIMARY = '/_pkey$/';

    public function columns(): iterable
    {
        $columns =
            $this->getDatabaseConnection()
                ->prepare(
                    'SELECT DISTINCT ' .
                    '          c.column_name AS name, ' .
                    '          c.column_default AS defaultValue, ' .
                    '          CASE c.is_nullable WHEN \'YES\' THEN 1 ELSE 0 END AS isNullable, ' .
                    '          c.data_type AS type, ' .
                    '          k.constraint_name AS constraintName, ' .
                    '          cc.table_name AS referencedTableName, ' .
                    '          cc.column_name AS referencedColumnName ' .
                    'FROM      information_schema.columns AS c ' .
                    'LEFT JOIN information_schema.key_column_usage AS k ' .
                    'ON        k.table_catalog = c.table_catalog ' .
                    'AND       k.table_name = c.table_name ' .
                    'AND       k.table_schema = c.table_schema ' .
                    'AND       k.column_name = c.column_name ' .
                    'LEFT JOIN information_schema.constraint_column_usage AS cc ' .
                    'ON        cc.table_catalog = c.table_catalog ' .
                    'AND       cc.table_schema = c.table_schema ' .
                    'AND       cc.table_name = c.table_name ' .
                    'AND       cc.column_name = c.column_name ' .
                    'AND       cc.constraint_name = k.constraint_name ' .
                    'WHERE     c.table_catalog = :database_name ' .
                    'AND       c.table_name = :table_name ' .
                    'AND       c.table_schema = :table_schema',
                    [
                        PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL
                    ]
                )
                ->execute([
                    'database_name' => $this->databaseName,
                    'table_name'    => $this->name,
                    'table_schema'  => 'public'
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
