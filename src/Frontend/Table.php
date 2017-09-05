<?php

declare(strict_types=1);

namespace Hywan\DatabaseToPlantUML\Frontend;

use Hoa\Database\Dal;
use Hoa\Database\DalStatement;
use Hoa\Visitor;

abstract class Table implements Visitor\Element
{
    protected $_databaseConnection = null;

    public $databaseName;
    public $name;
    public $engine  = null;
    public $comment = '';

    public function __construct(Dal $databaseConnection)
    {
        $this->_databaseConnection = $databaseConnection;
    }

    abstract public function columns(): iterable;

    public function getDatabaseConnection(): Dal
    {
        return $this->_databaseConnection;
    }

    public function accept(Visitor\Visit $visitor, &$handle = null, $eldnah = null)
    {
        return $visitor->visit($this, $handle, $eldnah);
    }
}
