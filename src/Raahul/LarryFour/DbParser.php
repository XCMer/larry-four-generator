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


    /**
     * For each table provided, parse it and add them to the migrationList. This function
     * can be called multiple times and it will keep adding tables to the same migrationList
     * @param  array  $tables An associative array of table name and an array of its columns
     * @return MigrationList  The LarryFour migrationList object
     */
    public function parse($tables)
    {
        foreach ($tables as $tableName => $columns)
        {
            $this->createTableMigration($tableName, $columns);
        }

        return $this->migrationList;
    }


    /**
     * For a given table, generate the migration object with the appropriate
     * parameters
     * @param  string $tableName The name of the table
     * @param  array  $columns   An array of SchemaExtractor Column objects
     */
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
                'unsigned' => $column->unsigned,
                'nullable' => $column->null,
                'primary' => ( $column->index == 'primary' ),
                'unique' => ( $column->index == 'unique' ),
                'index' => ( $column->index == 'multicolumn' )
            );

            $this->migrationList->addColumn($modelName, $c);
        }
    }


    /**
     * Given an mysql data type, return the laravel SchemaBuilder name for the
     * same
     * @param  Column $column The column object
     * @return string         The type of the column as denoted in Laravel
     */
    private function getLaravelColumnType($column)
    {
        // Special handling for integers that can be primary keys
        if (($column->type == 'int') && ($column->autoIncrement))
        {
            return 'increments';
        }

        // Special handling for tinyints with precision 1, that can be bools
        if (($column->type == 'tinyint'))
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


    /**
     * Get the column parameters as it should be provided in Laravel, since some columns
     * don't have parameters specified in Laravel even though in the table, they do
     * @param  array  $parameters An array of parameters to the column
     * @param  string $type       The type of the column
     * @return array              Either the parameters or a blank array
     */
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