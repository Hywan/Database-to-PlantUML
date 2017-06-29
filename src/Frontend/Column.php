<?php

declare(strict_types=1);

namespace Hywan\DatabaseToPlantUML\Frontend;

use Hoa\Visitor;

class Column implements Visitor\Element
{
    const PRIMARY = 'PRIMARY';

    public $name;
    public $defaultValue = null;
    public $isNullable = '';
    public $type = '';
    public $constraintName = null;
    public $referencedTableName = null;
    public $referencedColumnName = null;

    public function accept(Visitor\Visit $visitor, &$handle = null, $eldnah  = null)
    {
        return $visitor->visit($this, $handle, $eldnah);
    }
}
