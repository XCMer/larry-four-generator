<?php namespace Raahul\LarryFour;

use \Raahul\LarryFour\Exception\ParseError;

class Parser
{
    /**
     * Instance of the field parser
     * @var \Raahul\LarryFour\Parser\FieldParser
     */
    private $fieldParser;

    /**
     * Instance of the model definition parser
     * @var \Raahul\LarryFour\Parser\ModelDefinitionParser
     */
    private $modelDefinitionParser;

    /**
     * Instance of the model list object
     * @var \Raahul\LarryFour\ModelList
     */
    private $modelList;

    /**
     * Instance of the migration list object
     * @var \Raahul\LarryFour\MigrationList
     */
    private $migrationList;

    /**
     * An array holding relations between models and other meta data about them
     * @var array
     */
    private $relations;


    public function __construct(\Raahul\LarryFour\Parser\FieldParser $fieldParser,
                    \Raahul\LarryFour\Parser\ModelDefinitionParser $modelDefinitionParser,
                    \Raahul\LarryFour\ModelList $modelList,
                    \Raahul\LarryFour\MigrationList $migrationList)
    {
        $this->fieldParser = $fieldParser;
        $this->modelDefinitionParser = $modelDefinitionParser;
        $this->modelList = $modelList;
        $this->migrationList = $migrationList;
    }


    /**
     * Parses the contents of an actual input file for Larry and returns an array
     * of Models and Migrations that can then be used to generate those files
     * @param  string $input The input text
     * @return array         An array of modelList and migrationList that has been generated
     */
    public function parse($input)
    {
        // Track the current line for showing errors
        $currentLine = 0;

        // Start parsing
        // Reset the relations
        $this->relations = array();

        // Replace Windows line endings with Linux newlines
        $input = str_replace("\r\n", "\n", $input);

        // Explode input into different lines
        $lines = explode("\n", $input);

        // Start parsing them line by line
        $currentModel = null;
        $currentModelType = null;
        foreach ($lines as $line)
        {
            // Increment current line count
            $currentLine++;

            // Ignore blank lines
            if (!trim($line)) continue;

            // Determine if a line is a model definition or a field definition
            // Model definitions should start without a whitespace, while field definitions
            // should be indented to an arbitrary extent
            if (preg_match('/^\s/', $line))
            {
                // Line is a field definition
                // Check if we have a model to work with
                if (!$currentModel) die('Field definitions appearing before a model is defined.');

                // Else, let's start working on the field
                try {
                    $this->parseFieldDefinitionLine($line, $currentModel, $currentModelType);
                }
                catch (ParseError $e) {
                    throw new ParseError("[Line $currentLine] " . $e->getMessage());
                }
            }
            else
            {
                // Line is a model definition
                // Parse it and set the current model
                try {
                    list($currentModel, $currentModelType)
                        = $this->parseModelDefinitionLine($line);
                }
                catch (ParseError $e) {
                    throw new ParseError("[Line $currentLine] " . $e->getMessage());
                }
            }
        }

        // Process all the relations and add in the respective columns
        // to migration
        $this->processRelations();

        // Return result
        return array(
            'modelList' => $this->modelList,
            'migrationList' => $this->migrationList
        );
    }


    /**
     * Parse the model definition line and append the results to the appropriate
     * data structure
     * @param  string $line The input line to be parsed
     * @return array        The name of the model/table that was parsed and the
     *                      the definition type (model/table)
     */
    public function parseModelDefinitionLine($line)
    {
        // Parse the model definition
        $parsed = $this->modelDefinitionParser->parse(trim($line));

        // Figure out whether it is a table or a model definition.
        $modelName = $parsed['modelName'];
        $tableName = $parsed['tableName'];
        $type = $parsed['type'];

        // In case of a model definition:
        // Create a new model and migration
        // The table name can either be blank or overriden.
        // Process relations
        // Return modelName as the model name, and the type
        if ($type == 'model')
        {
            $this->modelList->create($modelName, $tableName);
            $this->migrationList->create($modelName, $tableName);

            // Detect and add in relations for later use
            foreach ($parsed['relations'] as $rel)
            {
                $this->relations[] = array_merge(
                    array('fromModel' => $modelName),
                    $rel
                );
            }

            // Return model name and type
            return array($modelName, $type);
        }
        // In case of a table definition:
        // Create just the migration with the model name as the same as
        // the table name
        else
        {
            $this->migrationList->create($tableName, $tableName);

            // Return the tableName, type pair
            return array($tableName, $type);
        }
    }


    /**
     * Parse the field definition line and build the necessary data structures
     * @param  string $line  The field line to be parsed
     * @param  string $model The name of the model to which the field belongs
     * @param  string $type  The type of the definition: model/table
     * @return void
     */
    public function parseFieldDefinitionLine($line, $model, $type)
    {
        // Is this a model definition, or a table definition?
        // We won't add anything to the model list in case of a table definition
        $isModelDefinition = ($type == 'model');

        // Parse the field definition
        $parsed = $this->fieldParser->parse(trim($line));

        // Check for errors
        if (!$parsed)
        {
            throw new ParseError("Could not parse field line. Check for errors like misplaced quotes.");
        }

        // Check if the field is timestamps
        if ($parsed['type'] == 'timestamps')
        {
            $isModelDefinition && $this->modelList->setTimestamps($model);
            $this->migrationList->setTimestamps($model);
            return;
        }

        // Check if the field is softDeletes
        if ($parsed['type'] == 'softDeletes')
        {
            $isModelDefinition && $this->modelList->setSoftDeletes($model);
            $this->migrationList->setSoftDeletes($model);
            return;
        }

        // Check if field type is increments
        if ($parsed['type'] == 'increments')
        {
            $isModelDefinition && $this->modelList->setPrimaryKey($model, $parsed['name']);
            $this->migrationList->setPrimaryKey($model, $parsed['name']);
            return;
        }


        // For other fields, add them to the current migration
        $this->migrationList->addColumn($model, $parsed);
    }


    /**
     * Add in relational fields to all the migrations and models by processing the relations
     * that were find during parsing
     * @return void
     *
     */
    public function processRelations()
    {
        foreach ($this->relations as $rel)
        {
            // If the relations are of type hm, ho
            if (in_array($rel['relationType'], array('hm', 'ho')))
            {
                $this->processHasRelation($rel);
            }

            // Else if relation type is btm or btmc
            else if (in_array($rel['relationType'], array('btm', 'btmc')))
            {
                $this->processBelongsToManyRelation($rel);
            }

            // Else if type of the relation is polymorphic
            else if (in_array($rel['relationType'], array('mm', 'mo')))
            {
                $this->processPolymorphicRelation($rel);
            }
        }
    }


    /**
     * Add the necessary columns to the migration and functions to the models
     * for a has one or has many relation
     * @param  array $rel An element of the relation array that is being processed
     */
    private function processHasRelation($rel)
    {
        // If the relations are of type hm, ho, then the column appears
        // in the related table
        // Add in the key
        $this->migrationList->addForeignKey(
            $rel['relatedModel'],
            $rel['fromModel'],
            $rel['foreignKey']
        );

        // Add in the hm/ho function to the fromModel and the bt
        // function to the related model
        $this->modelList->addFunction(
            $rel['fromModel'],
            $rel['relatedModel'],
            $rel['relationType'],
            $rel['foreignKey']
        );
        $this->modelList->addFunction(
            $rel['relatedModel'],
            $rel['fromModel'],
            'bt',
            $rel['foreignKey']
        );
    }


    /**
     * Add the necessary columns to the migration and functions to the models
     * for a belongs to many relation, checking if a pivot table has to be created,
     * depending on whether the type is 'btm' or 'btmc'
     * @param  array $rel An element of the relation array that is being processed
     */
    private function processBelongsToManyRelation($rel)
    {
        // Determine if a pivot table has to be created
        $createTable = ($rel['relationType'] == 'btm');

        // Determine the name of the pivot table
        // If we're given a pivot table override, use that
        if ($rel['pivotTable'])
        {
            $pivotTableName = $rel['pivotTable'];
        }
        else
        {
            $pivotTableName =
                ( strcmp($rel['fromModel'], $rel['relatedModel']) < 0 )
                ? strtolower($rel['fromModel'] . '_' . $rel['relatedModel'])
                : strtolower($rel['relatedModel'] . '_' . $rel['fromModel']);
        }

        // Add in the new table if it is needed
        $createTable && $this->migrationList->create($pivotTableName, $pivotTableName);


        // Add in the columns
        // Take care to override column names if they have been provided
        if ($rel['foreignKey'])
        {
            $pivotColumn1 = $rel['foreignKey'][0];
            $pivotColumn2 = $rel['foreignKey'][1];
        }
        else
        {
            $pivotColumn1 = strtolower($rel['fromModel'] . '_id');
            $pivotColumn2 = strtolower($rel['relatedModel'] . '_id');
        }

        // If table is not need, make sure that the table already exists, and has
        // the necessary columns
        if (!$createTable)
        {
            // Check if the table exists
            if (!$this->migrationList->exists($pivotTableName))
            {
                throw new ParseError("Custom pivot table '{$pivotTableName}' specified in model '"
                    . $rel['fromModel'] . "'' does not exist.");
            }

            // Check if the columns exist
            $pivotMigration = $this->migrationList->get($pivotTableName);
            if (!$pivotMigration->columnExists($pivotColumn1))
            {
                throw new ParseError("Custom pivot table '{$pivotTableName}' does not contain the necessary column: {$pivotColumn1}");
            }
            if (!$pivotMigration->columnExists($pivotColumn2))
            {
                throw new ParseError("Custom pivot table '{$pivotTableName}' does not contain the necessary column: {$pivotColumn2}");
            }

            // Check column data types
            if (!($pivotMigration->getColumnType($pivotColumn1) == 'integer')
                or !($pivotMigration->isColumnUnsigned($pivotColumn1))
                )
            {
                throw new ParseError("Column '{$pivotColumn1}' in custom pivot table '{$pivotTableName}' needs to be of type unsigned integer");
            }
            if (!($pivotMigration->getColumnType($pivotColumn2) == 'integer')
                or !($pivotMigration->isColumnUnsigned($pivotColumn2))
                )
            {
                throw new ParseError("Column '{$pivotColumn2}' in custom pivot table '{$pivotTableName}' needs to be of type unsigned integer");
            }
        }

        // Then add in the two columns if a table has to be created
        $createTable && $this->migrationList->addForeignKey(
            $pivotTableName,
            null,
            $pivotColumn1
        );

        $createTable && $this->migrationList->addForeignKey(
            $pivotTableName,
            null,
            $pivotColumn2
        );


        // Manage the model function, which has to added regardless of whether
        // we're using a custom pivot or not
        // First, if we're using btmc, fetch additional columns that needs to go
        // with the pivot
        $additional = array('btmcColumns' => array(), 'btmcTimestamps' => false);
        if (!$createTable)
        {
            $pivotColumns = $this->migrationList->get($pivotTableName)->all();
            foreach ($pivotColumns as $name => $data)
            {
                // We should not add the pivot columns
                if (!in_array( $name, array($pivotColumn1, $pivotColumn2) ))
                {
                    $additional['btmcColumns'][] = $name;
                }
            }

            // Also check if timestamps are enabled
            if ($this->migrationList->get($pivotTableName)->timestamps)
            {
                $additional['btmcTimestamps'] = true;
            }
        }

        $this->modelList->addFunction(
            $rel['fromModel'],
            $rel['relatedModel'],
            $rel['relationType'],
            $rel['foreignKey'],
            $rel['pivotTable'],
            $additional
        );
    }


    /**
     * Add the necessary columns to the migration and functions to the models
     * for a polymorphic relation
     * @param  array $rel An element of the relation array that is being processed
     */
    private function processPolymorphicRelation($rel)
    {
        // Add in two columns to the related model
        // A foreign key is required to be specified in this case
        $this->migrationList->addForeignKey(
            $rel['relatedModel'],
            $rel['fromModel'],
            $rel['foreignKey'] . '_id'
        );

        $this->migrationList->addForeignKey(
            $rel['relatedModel'],
            $rel['fromModel'],
            $rel['foreignKey'] . '_type',
            'string'
        );


        // Add functions in both the models
        // The relatedModel is where we need to add the morphTo function
        // with the same name as the foreignKey
        $this->modelList->addFunction(
            $rel['relatedModel'],
            null, // The morphTo function doesn't relate to any specific model
            'mt',
            $rel['foreignKey']
        );

        // Now add the appropriate function to the current table
        $this->modelList->addFunction(
            $rel['fromModel'],
            $rel['relatedModel'],
            $rel['relationType'],
            $rel['foreignKey']
        );
    }
}