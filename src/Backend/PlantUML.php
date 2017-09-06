<?php

declare(strict_types=1);

namespace Hywan\DatabaseToPlantUML\Backend;

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
            '!define table(x) class x << (T,#ffebf3) >>' . "\n" .
            'hide methods' . "\n" .
            'hide stereotypes' . "\n" .
            'skinparam classFontColor #3b0018' . "\n" .
            'skinparam classArrowColor #ff0066' . "\n" .
            'skinparam classBorderColor #ff0066' . "\n" .
            'skinparam classBackgroundColor ##f6f4ee' . "\n" .
            'skinparam shadowing false' . "\n" .
            "\n";

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

        $listedColumns = [];

        foreach ($columns as $column) {
            $isPrimary = 0 !== preg_match($column::PRIMARY, $column->constraintName ?? '');

            if (false === in_array($column->name, $listedColumns)) {
                $out .= sprintf(
                    '    {field} %s%s%s%s%s' . "\n",
                    $isPrimary ? '+' : '',
                    $column->name,
                    str_repeat("\t", max(1, $maximumTabulation - (int) (floor(strlen($column->name) / 4)))),
                    $column->isNullable ? '?' : '',
                    $column->type
                );

                $listedColumns[] = $column->name;
            }

            if (false === $isPrimary &&
                null !== $column->referencedTableName &&
                null !== $column->referencedColumnName) {
                $connections .=
                    $column->referencedTableName . ' <-- ' . $table->name .
                    ' : on ' . $column->name . ' = ' . $column->referencedColumnName . "\n";
            }
        }

        $out .=
            '}' . "\n\n" .
            $connections;

        return $out;
    }
}
