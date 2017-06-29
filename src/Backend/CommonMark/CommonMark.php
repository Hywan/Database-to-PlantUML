<?php

declare(strict_types=1);

namespace Hywan\DatabaseToPlantUML\Backend\CommonMark;

use Hywan\DatabaseToPlantUML\Frontend;
use Hoa\Visitor;

class CommonMark implements Visitor\Visit
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
        $out = '# `' . $database->name . '`' . "\n\n";

        foreach ($database->tables() as $table) {
            $out .= $table->accept($this, $handle, $eldnah) . "\n";
        }

        return $out;
    }

    public function visitTable(Frontend\Table $table, &$handle = null, &$eldnah = null): string
    {
        $out = '## `' . $table->name . '`' . "\n\n";

        if (!empty($table->comment)) {
            $out .= $table->comment;
        }

        $columnNames         = array_keys(get_class_vars(Frontend\Column::class));
        $separators          = [];
        $maximumColumnWidths = [];

        foreach ($columnNames as $columnName) {
            $maximumColumnWidths[$columnName] = strlen($columnName);
        }

        $columns = [];

        foreach ($table->columns() as $column) {
            $columns[] = $column;

            foreach ($columnNames as $columnName) {
                $maximumColumnWidths[$columnName] = max($maximumColumnWidths[$columnName], strlen($column->$columnName ?: ''));
            }
        }

        $pattern = '';

        foreach ($maximumColumnWidths as $maximumColumnWidth) {
            $length = $maximumColumnWidth + 2;

            $pattern      .= ' %-' . $length . 's |';
            $separators[]  = str_repeat('-', $length);
        }

        $out .=
            '|' . sprintf($pattern, ...$columnNames) . "\n" .
            '|' . sprintf($pattern, ...$separators)  . "\n";

        foreach ($columns as $column) {
            $columnValues = array_map(
                function ($value) {
                    if ('' === $value || null === $value) {
                        return '';
                    }

                    return '`' . $value . '`';
                },
                array_values(get_object_vars($column))
            );

            $out .= '|' . sprintf($pattern, ...$columnValues) . "\n";
        }

        return $out;
    }
}
