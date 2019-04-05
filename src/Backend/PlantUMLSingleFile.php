<?php

declare(strict_types=1);

namespace Hywan\DatabaseToPlantUML\Backend;

use Hywan\DatabaseToPlantUML\Frontend;
use Hoa\Visitor;

/**
 * Class that writes the definitions into single files:
 * - definition-file: table__[database]__[tablename].iuml
 * - relations-file: relations__[database]__[tablename]__[referenced_table].iuml
 * - file including the definition and the first level of relation: table__[database]__[tablename].puml
 *
 * The output is then just a file with lots of includes to all puml-files
 */
class PlantUMLSingleFile implements Visitor\Visit
{
    /**
     * Stores the database this visitor is visiting so its possible to use this value
     *
     * @var name
     */
    protected $databaseName;

    protected $definitions = '@startuml' . "\n\n" .
        '!ifdef !table' . "\n" .
        '!define table(x) class x << (T,#ffebf3) >>' . "\n" .
        'hide methods' . "\n" .
        'hide stereotypes' . "\n" .
        'skinparam classFontColor #3b0018' . "\n" .
        'skinparam classArrowColor #ff0066' . "\n" .
        'skinparam classBorderColor #ff0066' . "\n" .
        'skinparam classBackgroundColor ##f6f4ee' . "\n" .
        'skinparam shadowing false' . "\n" .
        '!endif' . "\n" .
        "\n";

    protected $fileBanner = "@startuml\n\n!include definitions.iuml\n\n";

    protected $fileEnd = '@enduml';

    public function visit(Visitor\Element $element, &$handle = null, $eldnah = null)
    {
        file_put_contents('definitions.iuml', $this->definitions.$this->fileEnd);

        if ($element instanceof Frontend\Database) {
            return $this->visitDatabase($element, $handle, $eldnah);
        } elseif ($element instanceof Frontend\Table) {
            return $this->visitTAble($element, $handle, $eldnah);
        }

        throw new \RuntimeException('Unknown element to visit ' . get_class($element) . '.');
    }

    public function visitDatabase(Frontend\Database $database, &$handle = null, &$eldnah = null): string
    {
        $this->databaseName = $database->name;

        $out = $this->fileBanner . "\n";

        foreach ($database->tables() as $table) {
            $out .= "!include ".$table->accept($this, $handle, $eldnah) . "\n";
        }

        $out .= '@enduml';

        return $out;
    }

    public function visitTable(Frontend\Table $table, &$handle = null, &$eldnah = null): string
    {
        $out = $this->fileBanner."\n";

        $out.= '!ifdef !table_' . $table->name. "\n\n";
        $out .= 'table(' . $table->name . ') {' . "\n";
        $connections = [];

        $columns = [];
        $maximumNameLength = 0;

        foreach ($table->columns() as $column) {
            $columns[] = $column;

            $maximumNameLength = max($maximumNameLength, strlen($column->name));
        }

        $maximumTabulation = 1 + (int)floor($maximumNameLength / 4);

        $listedColumns = [];

        foreach ($columns as $column) {
            $isPrimary = 0 !== preg_match($column::PRIMARY, $column->constraintName ?? '');

            if (false === in_array($column->name, $listedColumns)) {
                $out .= sprintf(
                    '    {field} %s%s%s%s%s' . "\n",
                    $isPrimary ? '+' : '',
                    $column->name,
                    str_repeat("\t", max(1, $maximumTabulation - (int)(floor(strlen($column->name) / 4)))),
                    $column->isNullable ? '?' : '',
                    $column->type
                );

                $listedColumns[] = $column->name;
            }

            if (false === $isPrimary &&
                null !== $column->referencedTableName &&
                null !== $column->referencedColumnName
            ) {
                $connections[$column->referencedTableName] = ' on ' . $column->name . ' = ' . $column->referencedColumnName;
            }
        }

        $out .= "\n}\n!endif\n".$this->fileEnd."\n";

        $out = $this->writeTablePumlFileWithIncludes($table, $out, $connections);

        return $out;
    }


    /**
     * @param \Hywan\DatabaseToPlantUML\Frontend\Table $table
     * @param string                                   $out
     * @param array                                    $connections
     * @return string
     */
    protected function writeTablePumlFileWithIncludes(Frontend\Table $table, string $out, array $connections): string
    {
        $tableDefinitionFile = $this->saveTableDefinition($out, $table->name);
        $tableConnectionsFiles = $this->saveTableConnections($connections, $table->name);

        $tablePumlFile = sprintf(
            'table__%s__%s.puml',
            $this->databaseName,
            $table->name
        );

        $out = $this->fileBanner."!include $tableDefinitionFile\n";
        if ($tableConnectionsFiles) {
            foreach ($connections as $referencedTable => $comment) {
                // Include the table definitions as well
                $out .= sprintf(
                    '!include table__%s__%s.iuml',
                    $this->databaseName,
                    $referencedTable
                )."\n";
            }

            foreach ($tableConnectionsFiles as $tableConnectionsFile) {
                $out .= "!include $tableConnectionsFile\n";
            }
        }

        $out.= $this->fileEnd;

        file_put_contents($tablePumlFile, $out);

        return $tablePumlFile;
    }

    protected function saveTableDefinition(string $tableDefinitionString, string $tableName) : string
    {
        $filename = sprintf(
            'table__%s__%s.iuml',
            $this->databaseName,
            $tableName
        );

        file_put_contents($filename, $tableDefinitionString);
        return $filename;
    }

    protected function saveTableConnections(array $connections, string $tableName) : array
    {
        if (!$connections) {
            return [];
        }

        $relationFilenames = [];

        foreach ($connections as $referencedTable => $comment)
        {
            $relationName = sprintf(
                'relations__%s__%s__%s',
                $this->databaseName,
                $tableName,
                $referencedTable
            );
            $relationFilename = $relationName.'.iuml';

            $relationFileContent = $this->buildTableConnectionPlantUMLstringWithGuardStatement(
                $tableName,
                $referencedTable,
                $comment,
                $relationName
            );

            file_put_contents($relationFilename, $relationFileContent);

            $relationFilenames[] = $relationFilename;
        }

        return $relationFilenames;
    }

    /**
     * @param string $tableName
     * @param string $referencedTable
     * @param string $comment
     * @param string $relationName
     * @return string
     */
    protected function buildTableConnectionPlantUMLstringWithGuardStatement(
        string $tableName,
        string $referencedTable,
        string $comment,
        string $relationName
    ) : string
    {
        $connectionsString = $tableName . ' --> ' . $referencedTable . ' : ' . $comment;
        $relationFileContent = "@startuml\n\n!ifdef !$relationName\n\n";
        $relationFileContent .= $connectionsString;
        $relationFileContent .= "\n!endif\n@enduml\n";
        return $relationFileContent;
    }
}
