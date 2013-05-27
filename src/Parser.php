<?php namespace LarryFour;

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


    public function __construct(\LarryFour\Parser\FieldParser $fieldParser,
                    \LarryFour\Parser\ModelDefinitionParser $modelDefinitionParser)
    {
        $this->fieldParser = $fieldParser;
        $this->modelDefinitionParser = $modelDefinitionParser;
    }


    /**
     * Parses the contents of an actual input file for Larry and returns an array
     * of Models and Migrations that can then be used to generate those files
     * @param  string $input The input text
     * @return [type]        [description]
     */
    public function parse($input)
    {
        // Track the current line for showing errors
        $currentLine = 0;

        // Start parsing
        // Prepare an output data structure
        $result = array();

        // For storing relations as we get them
        $relations = array();

        // Replace Windows line endings with Linux newlines
        $input = str_replace("\r\n", "\n", $input);

        // Explode input into different lines
        $lines = explode("\n", $input);

        // Start parsing them line by line
        $currentModel = null;
        foreach ($lines as $line)
        {
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
                $parsed = $this->fieldParser->parse(trim($line));

                // Check if the field is timestamps
                if ($parsed['type'] == 'timestamps')
                {
                    $result[$currentModel]['model']->timestamps = true;
                    $result[$currentModel]['migration']->timestamps = true;
                    continue;
                }

                // For other fields, add them to the current migration
                $result[$currentModel]['migration']->addColumn($parsed);
            }
            else
            {
                // Line is a model definition
                // Parse it
                $parsed = $this->modelDefinitionParser->parse(trim($line));

                // Create a new model and migration
                $modelName = $parsed['modelName'];
                $model = new Model($modelName, $parsed['tableName']);
                $migration = new Migration($model->tableName);

                $result[ $modelName ] = array(
                    'model' => $model,
                    'migration' => $migration
                );

                // Detect and add in relations for later use
                foreach ($parsed['relations'] as $rel)
                {
                    $relations[] = array_merge(
                        array('fromModel' => $modelName),
                        $rel
                    );
                }

                // Set it as the current model
                $currentModel = $modelName;
            }

            // Increment current line count
            $currentLine++;
        }

        // Process all the relations and add in the respective columns
        // to migration
        foreach ($relations as $rel)
        {
            // If the relations are of type hm, ho, then the column appears
            // in the related table
            if (in_array($rel['relationType'], array('hm', 'ho')))
            {
                // Use the overrided foreign key if present, or lowercase the
                // from model and append "_id"
                $foreignKey = $rel['foreignKey']
                    ? $rel['foreignKey']
                    : strtolower($rel['fromModel']) . '_id';

                // Add in the key
                $result[ $rel['relatedModel'] ]
                    ['migration']
                    ->addColumn(array(
                    'name' => $foreignKey,
                    'type' => 'integer',
                    'parameters' => array(),
                    'unsigned' => true
                ));
            }
            // Else if type of the relation is polymorphic
            if (in_array($rel['relationType'], array('mm', 'mo')))
            {
                // Add in two columns to the related model
                // A foreign key is required to be specified in this case
                $result[ $rel['relatedModel'] ]
                    ['migration']
                    ->addColumn(array(
                    'name' => $rel['foreignKey'] . '_id',
                    'type' => 'integer',
                    'parameters' => array(),
                    'unsigned' => true
                ));

                $result[ $rel['relatedModel'] ]
                    ['migration']
                    ->addColumn(array(
                    'name' => $rel['foreignKey'] . '_type',
                    'type' => 'string',
                    'parameters' => array()
                ));
            }
        }

        // Return the result
        return $result;
    }
}