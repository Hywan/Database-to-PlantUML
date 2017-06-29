<?php

declare(strict_types=1);

namespace Hywan\DatabaseToPlantUML\Frontend;

use Hoa\Database\Dal;
use Hoa\Database\DalStatement;
use Hoa\Visitor;
use PDO;

class Table implements Visitor\Element
{
    protected $_databaseConnection = null;

    public $databaseName;
    public $name;
    public $engine = null;
    public $comment = '';

    public function __construct(Dal $databaseConnection)
    {
        $this->_databaseConnection = $databaseConnection;
    }

    public function columns(): iterable
    {
        $columns =
            $this->_databaseConnection
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

    public function accept(Visitor\Visit $visitor, &$handle = null, $eldnah  = null)
    {
        return $visitor->visit($this, $handle, $eldnah);
    }
}
