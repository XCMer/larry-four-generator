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
        $result = array(
            'models' => array(),
        );

        // Replace Windows line endings with Linux newlines
        $input = str_replace("\r\n", "\n", $input);

        // Explode input into different lines
        $lines = explode("\n", $input);

        // Start parsing them line by line
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
            }
            else
            {
                // Line is a model definition
                // Parse it
                $parsed = $this->modelDefinitionParser->parse(trim($line));

                // Fill up the model details
                $result['models'][ $parsed['modelName'] ]
                    = new Model($parsed['modelName'], $parsed['tableName']);
            }

            // Increment current line count
            $currentLine++;
        }

        // Return the result
        return $result;
    }
}