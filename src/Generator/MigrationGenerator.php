<?php namespace LarryFour\Generator;

class MigrationGenerator
{
    /**
     * Stores the migration template for use throughout the lifetime of this instance,
     * which saves is from reading the template again and again from a file
     * @var string
     */
    private $template;


    /**
     * Load the migration template
     */
    public function __construct()
    {
        $this->template = file_get_contents(__DIR__ . '/templates/migration');
    }


    /**
     * Generate the migration file contents from the templates and the migration
     * object provided
     * @param  \LarryFour\Migration $migration The migration object whose migration file has to be generated
     * @return string                          The migration file contents
     */
    public function generate(\LarryFour\Migration $migration)
    {
        // Store the template locally
        $result = $this->template;

        // Replace the table name
        $result = str_replace('{{tableName}}', $migration->tableName, $result);

        // Replace the class name
        $className = 'Create' . ucwords($migration->tableName) . 'Table';
        $result = str_replace('{{className}}', $className, $result);

        // Populate the fields
        // First, the primary key
        $pkField = $this->getFieldLine(array(
            'name' => $migration->primaryKey,
            'type' => 'increments',
            'parameters' => array()
        ));
        $result = str_replace('{{fields}}', $pkField . "\n            {{fields}}", $result);

        foreach ($migration->all() as $column)
        {
            $result = str_replace('{{fields}}',
                $this->getFieldLine($column) . "\n            {{fields}}",
                $result
            );
        }

        // Remove the final fields tag
        $result = str_replace("\n            {{fields}}", '', $result);

        // Return it
        return $result;
    }

    private function getFieldLine($fieldData)
    {
        // The beginning till the function
        $result = '$table->'
            . $fieldData['type']
            . "('" . $fieldData['name'] . "'";

        // Additional parameters to the function
        if ($fieldData['parameters'])
        {
            if ($fieldData['type'] == 'enum')
            {
                // Add quotes around parameters for enum and put them in an array
                $result .= ', '
                    . 'array("'
                    . implode('", "', $fieldData['parameters'])
                    . '")';
            }
            else
            {
                $result .= ', ' . implode(', ', $fieldData['parameters']);
            }
        }

        // Closing of the field function
        $result .= ')';

        // Other modifiers
        $modifiers = array('default', 'nullable', 'unsigned', 'primary', 'fulltext',
            'unique', 'index');
        foreach ($modifiers as $m)
        {
            if ( isset($fieldData[$m]) )
            {
                $result  .= '->' . $m . '(';

                if ($m == 'default')
                {
                    $result .= '"' . $fieldData['default'] . '"';
                }

                $result .= ')';
            }
        }

        // The final semicolon
        $result .= ';';

        // Return it
        return $result;
    }
}