<?php namespace Raahul\LarryFour;

use \Raahul\SchemaExtractor\SchemaExtractor;
use \Raahul\LarryFour\MigrationList;
use \Illuminate\Support\Pluralizer;

class DbParser
{
    /**
     * Instance of the migration list object
     * @var \Raahul\LarryFour\MigrationList
     */
    private $migrationList;

    /**
     * Instance of the schema extractor
     * @var \Raahul\SchemaExtractor\SchemaExtractor
     */
    private $schemaExtractor;

    /**
     * Type of the database
     * @var string
     */
    private $dbType;


    public function __construct(MigrationList $migrationList, SchemaExtractor $schemaExtractor, $dbType)
    {
        $this->migrationList = $migrationList;
        $this->schemaExtractor = $schemaExtractor;
        $this->dbType = $dbType;
    }


    public function parse($tables)
    {
        foreach ($tables as $tableName => $columns)
        {
            $this->createTableMigration($tableName, $columns);
        }

        return $this->migrationList;
    }


    private function createTableMigration($tableName, $columns)
    {
        // Get the parsed columns
        $parsedColumns = $this->schemaExtractor->extract($columns, $this->dbType);

        // Get the model name form the table name
        $modelName = ucwords( Pluralizer::singular($tableName) );

        // Create a new migration
        $this->migrationList->create($modelName, $tableName);

        // Now, proceed towards adding columns
        foreach ($parsedColumns as $column)
        {
            $type = $this->getLaravelColumnType($column);

            // For primary keys, we simply set pK
            if ($type == 'increments')
            {
                $this->migrationList->setPrimaryKey($modelName, $column->name);
                continue;
            }

            $c = array(
                'name' => $column->name,
                'type' => $type,
                'parameters' => $this->getLaravelColumnParameters($column->parameters, $type),
                'default' => is_null($column->defaultValue) ? '' : $column->defaultValue,
                'nullable' => $column->null,
                'primary' => ( $column->index == 'primary' ),
                'unique' => ( $column->index == 'unique' ),
                'index' => ( $column->index == 'multicolumn' )
            );

            $this->migrationList->addColumn($modelName, $c);
        }
    }


    private function getLaravelColumnType($column)
    {
        // Special handling for integers that can be primary keys
        if (($column->type == 'int') && ($column->autoIncrement))
        {
            return 'increments';
        }

        // Special handling for tinyints with precision 1, that can be bools
        if (($column->type == 'tinyint') && ($column->parameters == array(1)))
        {
            return 'boolean';
        }

        // For other columns, we can map mysql types to laravel types. We only map
        // values that differ between mysql and laravel
        $mapping = array(
            'int' => 'integer',
            'bigint' => 'bigInteger',
            'smallint' => 'smallInteger',
            'varchar' => 'string',
            'datetime' => 'dateTime',
            'blob' => 'binary'
        );

        // Return a mapping, or the column type as is in cases where the type name
        // is the same in mysql and laravel
        return (
            isset($mapping[ $column->type ])
            ? $mapping[ $column->type ]
            : $column->type
        );
    }

    private function getLaravelColumnParameters($parameters, $type)
    {
        // Integers floats, and booleans don't have parameters
        if ( in_array($type, array('integer', 'smallInteger', 'bigInteger', 'boolean', 'float') ))
        {
            return array();
        }

        // For others, return the paramters as it is
        return $parameters;
    }
}