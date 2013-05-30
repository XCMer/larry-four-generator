<?php namespace LarryFour;

use \LarryFour\Exception\ParseError;

class Parser
{
    /**
     * Instance of the field parser
     * @var \LarryFour\Parser\FieldParser
     */
    private $fieldParser;

    /**
     * Instance of the model definition parser
     * @var \LarryFour\Parser\ModelDefinitionParser
     */
    private $modelDefinitionParser;

    /**
     * Instance of the model list object
     * @var \LarryFour\ModelList
     */
    private $modelList;

    /**
     * Instance of the migration list object
     * @var \LarryFour\MigrationList
     */
    private $migrationList;

    /**
     * An array holding relations between models and other meta data about them
     * @var array
     */
    private $relations;


    public function __construct(\LarryFour\Parser\FieldParser $fieldParser,
                    \LarryFour\Parser\ModelDefinitionParser $modelDefinitionParser,
                    \LarryFour\ModelList $modelList,
                    \LarryFour\MigrationList $migrationList)
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
                    $this->parseFieldDefinitionLine($line, $currentModel);
                }
                catch (ParseError $e) {
                    throw new ParseError("[Line $currentLine] " . $e->getMessage() ."\n");
                }
            }
            else
            {
                // Line is a model definition
                // Parse it and set the current model
                try {
                    $currentModel = $this->parseModelDefinitionLine($line);
                }
                catch (ParseError $e) {
                    throw new ParseError("[Line $currentLine] " . $e->getMessage() ."\n");
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
     * @return string       The name of the model that was parsed
     */
    public function parseModelDefinitionLine($line)
    {
        // Parse the model definition
        $parsed = $this->modelDefinitionParser->parse(trim($line));

        // Create a new model and migration
        $modelName = $parsed['modelName'];
        $tableName = $parsed['tableName'];

        $model = $this->modelList->create($modelName, $tableName);
        $this->migrationList->create($modelName, $model->tableName);

        // Detect and add in relations for later use
        foreach ($parsed['relations'] as $rel)
        {
            $this->relations[] = array_merge(
                array('fromModel' => $modelName),
                $rel
            );
        }

        // Return the model name
        return $modelName;
    }


    /**
     * Parse the field definition line and build the necessary data structures
     * @param  string $line  The field line to be parsed
     * @param  string $model The name of the model to which the field belongs
     * @return void
     */
    public function parseFieldDefinitionLine($line, $model)
    {
        // Parse the field definition
        $parsed = $this->fieldParser->parse(trim($line));

        // Check if the field is timestamps
        if ($parsed['type'] == 'timestamps')
        {
            $this->modelList->setTimestamps($model);
            $this->migrationList->setTimestamps($model);
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

            // Else if relation type is bt
            else if ($rel['relationType'] == 'bt')
            {
                $this->processBelongsToRelation($rel);
            }

            // Else if relation type is btm
            else if ($rel['relationType'] == 'btm')
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
     * for a belongs to relation
     * @param  array $rel An element of the relation array that is being processed
     */
    private function processBelongsToRelation($rel)
    {
        // The column appears in the same table
        $this->migrationList->addForeignKey(
            $rel['fromModel'],
            $rel['relatedModel'],
            $rel['foreignKey']
        );

        // Add in the bt function to the current model
        $this->modelList->addFunction(
            $rel['fromModel'],
            $rel['relatedModel'],
            $rel['relationType'],
            $rel['foreignKey']
        );
    }


    /**
     * Add the necessary columns to the migration and functions to the models
     * for a belongs to many relation
     * @param  array $rel An element of the relation array that is being processed
     *
     * TODO: Add handling for models
     */
    private function processBelongsToManyRelation($rel)
    {
        // Determine the name of the pivot table
        $pivotTableName =
            ( strcmp($rel['fromModel'], $rel['relatedModel']) < 0 )
            ? strtolower($rel['fromModel'] . '_' . $rel['relatedModel'])
            : strtolower($rel['relatedModel'] . '_' . $rel['fromModel']);

        // Add in the new table
        $this->migrationList->create($pivotTableName, $pivotTableName);

        // Add in the columns
        $this->migrationList->addForeignKey(
            $pivotTableName,
            null,
            strtolower($rel['fromModel'] . '_id')
        );

        $this->migrationList->addForeignKey(
            $pivotTableName,
            null,
            strtolower($rel['relatedModel'] . '_id')
        );
    }


    /**
     * Add the necessary columns to the migration and functions to the models
     * for a polymorphic relation
     * @param  array $rel An element of the relation array that is being processed
     *
     * TODO: Add handling for models
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
    }
}