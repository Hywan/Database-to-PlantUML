<?php

declare(strict_types=1);

namespace Hywan\DatabaseToPlantUML\Backend\PlantUML;

use Hywan\DatabaseToPlantUML\Frontend;
use Hoa\Visitor;

class PlantUML implements Visitor\Visit
{
    public function visit(Visitor\Element $element, &$handle = null, $eldnah = null)
    {
        if ($element instanceof Frontend\Database) {
            return $this->visitDatabase($element, $handle, $eldnah);
        } elseif ($element instanceof Frontend\Table) {
            return $this->visitTAble($element, $handle, $eldnah);
        }

        throw new \RuntimeException('Unknown element to visit ' . get_class($element) . '.');
    }

    public function visitDatabase(Frontend\Database $database, &$handle = null, &$eldnah = null): string
    {
        $out =
            '@startuml' . "\n\n" .
            '!define table(x) class x << (T,#FFAAAA) >>' . "\n" .
            'hide methods' . "\n" .
            'hide stereotypes' . "\n\n";

        foreach ($database->tables() as $table) {
            $out .= $table->accept($this, $handle, $eldnah) . "\n";
        }

        $out .= '@enduml';

        return $out;
    }

    public function visitTable(Frontend\Table $table, &$handle = null, &$eldnah = null): string
    {
        $out         = 'table(' . $table->name . ') {' . "\n";
        $connections = '';

        $columns = [];
        $maximumNameLength = 0;

        foreach ($table->columns() as $column) {
            $columns[] = $column;

            $maximumNameLength = max($maximumNameLength, strlen($column->name));
        }

        $maximumTabulation = 1 + (int) floor($maximumNameLength / 4);

        foreach ($columns as $column) {
            $out .= sprintf(
                '    {field} %s%s%s%s%s' . "\n",
                $column::PRIMARY === $column->constraintName ? '+' : '',
                $column->name,
                str_repeat("\t", max(1, $maximumTabulation - (int) (floor(strlen($column->name) / 4)))),
                $column->isNullable ? '?' : '',
                $column->type
            );

            if (null !== $column->referencedTableName &&
                null !== $column->referencedColumnName) {
                $connections .=
                    $column->referencedTableName . ' *-- ' . $table->name .
                    ' : on ' . $column->name . ' = ' . $column->referencedColumnName . "\n";
            }
        }

        $out .=
            '}' . "\n\n" .
            $connections;

        return $out;
    }
}
