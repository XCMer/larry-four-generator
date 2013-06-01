<?php namespace LarryFour\Generator;

class ModelGenerator
{
    /**
     * Stores the model template for use throughout the lifetime of this instance,
     * which saves is from reading the template again and again from a file
     * @var string
     */
    private $modelTemplate;

    /**
     * Stores the relational function template for use throughout the lifetime of this instance,
     * which saves is from reading the template again and again from a file
     * @var string
     */
    private $relationalFunctionTemplate;


    /**
     * Load the model template
     */
    public function __construct()
    {
        $this->modelTemplate = file_get_contents(__DIR__ . '/templates/model');
        $this->relationalFunctionTemplate = file_get_contents(__DIR__ . '/templates/relational_function');
    }


    // TODO: add support for tableName
    public function generate($model)
    {
        // Store the local version of the template
        $result = $this->modelTemplate;

        // Add in the model name
        $result = $this->addModelName($result, $model->modelName);

        // Add in the timestamps
        $result = $this->addTimestampsIfNeeded($result, $model->timestamps);

        // Add in the primary key if needed
        $result = $this->addPrimaryKeyIfNeeded($result, $model->primaryKey);

        // Add in the table name if needed
        $result = $this->addTableNameIfNeeded($result, $model->tableName);

        // Add in all the functions
        foreach ($model->all() as $functionName => $functionData)
        {
            $functionBlock = $this->getRelationFunction($functionName, $functionData);
            $result = $this->addRelationFunction($result, $functionBlock);
        }

        // Remove the extraneous relational function tag
        $result = $this->removeRelationFunctionTag($result);

        // Return the result
        return $result;
    }

    private function addModelName($modelFileContents, $modelName)
    {
        return str_replace('{{modelName}}', $modelName, $modelFileContents);
    }

    private function addTimestampsIfNeeded($modelFileContents, $timestamps)
    {
        if ($timestamps)
        {
            $t = 'public $timestamps = true;';
        }
        else
        {
            $t = 'public $timestamps = false;';
        }

        return str_replace('{{timestamps}}',
            $t,
            $modelFileContents
        );
    }

    private function addPrimaryKeyIfNeeded($modelFileContents, $primaryKey)
    {
        if ($primaryKey == 'id')
        {
            return str_replace('    {{primaryKey}}' . "\n", '', $modelFileContents);
        }
        else
        {
            return str_replace('{{primaryKey}}',
                "public \$primaryKey = '{$primaryKey}';",
                $modelFileContents
            );
        }
    }

    private function addTableNameIfNeeded($modelFileContents, $tableName)
    {
        if ($tableName)
        {
            return str_replace('{{tableName}}',
                "protected \$table = '{$tableName}';",
                $modelFileContents
            );
        }
        else
        {
            return str_replace('    {{tableName}}' . "\n", '', $modelFileContents);
        }
    }

    private function getRelationFunction($functionName, $functionData)
    {
        // Store the template locally
        $result = $this->relationalFunctionTemplate;

        // Add in the function name
        $result = str_replace('{{functionName}}', $functionName, $result);

        // Create the function body
        $functionBody = 'return $this->'
            . $this->getFunctionNameFromRelationType($functionData['relationType'])
            . "('" . $functionData['toModel'] . "'" ;

        // Add in any extra parameters
        // For belongs to many, we have the table name override first, and then
        // the foreign keys. For everything else, there is just one foreign key
        //
        // First, check if it is a belongsToMany
        if ($functionData['relationType'] == 'btm')
        {
            // Check if a pivot table is provided
            if ($functionData['pivotTable'])
            {
                // Add that in first
                $functionBody .= ", '" . $functionData['pivotTable'] . "'";

                // Now check if we also have the two foreign keys
                if ($functionData['foreignKey'])
                {
                    // Add them as well
                    $functionBody .= ", '" . $functionData['foreignKey'][0] . "'"
                        . ", '" . $functionData['foreignKey'][1] . "'";
                }
            }
        }
        // For all other relations, check if a foreign key is override is present, and
        // append it
        else
        {
            if ($functionData['foreignKey'])
            {
                $functionBody .= ", '" . $functionData['foreignKey'] . "'";
            }
        }

        // Close the parenthesis and add a semicolon
        $functionBody .= ');';


        // Add the function body to the function template
        $result = str_replace('{{functionBody}}', $functionBody, $result);


        // Return the final function block
        return $result;
    }

    private function getFunctionNameFromRelationType($relationType)
    {
        switch ($relationType)
        {
            case 'ho':
                return 'hasOne';

            case 'hm':
                return 'hasMany';

            case 'bt':
                return 'belongsTo';

            case 'mo':
                return 'morphOne';

            case 'mm':
                return 'morphMany';

            case 'mt':
                return 'morphTo';

            case 'btm':
                return 'belongsToMany';
        }
    }

    private function addRelationFunction($modelFileContents, $functionBlock)
    {
        return str_replace('    {{relationalFunctions}}',
            $functionBlock . "\n" . '    {{relationalFunctions}}',
            $modelFileContents
        );
    }

    private function removeRelationFunctionTag($modelFileContents)
    {
        return str_replace("\n" . '    {{relationalFunctions}}',
            '',
            $modelFileContents
        );
    }
}