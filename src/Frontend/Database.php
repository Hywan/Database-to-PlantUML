<?php

declare(strict_types=1);

namespace Hywan\DatabaseToPlantUML\Frontend;

use Hoa\Database\Dal;
use Hoa\Database\DalStatement;
use Hoa\Visitor;

abstract class Database implements Visitor\Element
{
    protected $_databaseConnection = null;
    public $name;

    public function __construct(Dal $databaseConnection, string $name)
    {
        $this->_databaseConnection = $databaseConnection;
        $this->name                = $name;
    }

    abstract public function tables(): iterable;

    public function getDatabaseConnection(): Dal
    {
        return $this->_databaseConnection;
    }

    public function accept(Visitor\Visit $visitor, &$handle = null, $eldnah = null)
    {
        return $visitor->visit($this, $handle, $eldnah);
    }
}
