<?php

declare(strict_types=1);

namespace Hywan\DatabaseToPlantUML\Frontend;

use Hoa\Database\Dal;
use Hoa\Database\DalStatement;
use PDO;

class Table
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

    public function rows()
    {
        $rows =
            $this->_databaseConnection
                ->prepare(
                    'SELECT   column_name AS name, ' .
                    '         column_default AS defaultValue, ' .
                    '         is_nullable AS isNullable, ' .
                    '         column_type AS type ' .
                    'FROM     information_schema.columns ' .
                    'WHERE    table_schema = :database_name ' .
                    'AND      table_name = :table_name ' .
                    'ORDER BY ordinal_position ASC',
                    [
                        PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL
                    ]
                )
                ->execute([
                    'database_name' => $this->databaseName,
                    'table_name'    => $this->name
                ]);

        $rows->setFetchingStyle(
            DalStatement::FROM_START,
            DalStatement::FORWARD,
            DalStatement::AS_CLASS,
            Row::class
        );

        yield from $rows;
    }
}
