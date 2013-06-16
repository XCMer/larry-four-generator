<?php namespace Raahul\LarryFour\Generator;

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
        // Load the model template
        $this->modelTemplate = file_get_contents(__DIR__ . '/templates/model');

        // Load the relation function block template
        $this->relationalFunctionTemplate =
            file_get_contents(__DIR__ . '/templates/relational_function');
    }


    /**
     * Generate the model file contents from the templates and the model
     * object provided
     * @param  \Raahul\LarryFour\Model $model The model object whose model file has to be generated
     * @return string                  The model file contents
     */
    public function generate($model)
    {
        // Store the local version of the template
        $result = $this->modelTemplate;

        // Add in the model name
        $result = $this->addModelName($result, $model->modelName);

        // Add in the timestamps
        $result = $this->addTimestampsIfNeeded($result, $model->timestamps);

        // Add in the softDeletes
        $result = $this->addSoftDeletesIfNeeded($result, $model->softDeletes);

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


    /**
     * Given the model file contents, put in the model name in the appropriate
     * location
     * @param  string $modelFileContents The contents of the model file
     * @param  string $modelName         The name of the model
     * @return string                    The updated model file contents
     */
    private function addModelName($modelFileContents, $modelName)
    {
        return str_replace('{{modelName}}', $modelName, $modelFileContents);
    }


    /**
     * Given the model file contents, put in the timestamps in the appropriate
     * location if needed
     * @param  string  $modelFileContents The contents of the model file
     * @param  boolean $timestamps        Whether timestamps are needed
     * @return string                    The updated model file contents
     */
    private function addTimestampsIfNeeded($modelFileContents, $timestamps)
    {
        // Always explicitly set the timestamps field to true or false
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


    /**
     * Given the model file contents, put in the softDelete in the appropriate
     * location if needed
     * @param  string  $modelFileContents The contents of the model file
     * @param  boolean $timestamps        Whether softDelete is needed
     * @return string                     The updated model file contents
     */
    private function addSoftDeletesIfNeeded($modelFileContents, $softDeletes)
    {
        // If softDeletes is enabled, add in the line to enable it, else remove
        // the tag. We set this only when true
        if ($softDeletes)
        {
            return str_replace('{{softDeletes}}',
                "protected \$softDelete = true;",
                $modelFileContents
            );
        }

        // Else, add in the primary key line overriding the defaults
        else
        {
            return str_replace("    {{softDeletes}}\n", '', $modelFileContents);
        }
    }


    /**
     * Given the model file contents, put in the primary key override in the appropriate
     * location if needed
     * @param  string  $modelFileContents The contents of the model file
     * @param  string $primaryKey         The primary key of the model
     * @return string                     The updated model file contents
     */
    private function addPrimaryKeyIfNeeded($modelFileContents, $primaryKey)
    {
        // If the primary key is id, simply remove the primary key line along
        // with its newline
        if ($primaryKey == 'id')
        {
            return str_replace("    {{primaryKey}}\n", '', $modelFileContents);
        }

        // Else, add in the primary key line overriding the defaults
        else
        {
            return str_replace('{{primaryKey}}',
                "public \$primaryKey = '{$primaryKey}';",
                $modelFileContents
            );
        }
    }


    /**
     * Given the model file contents, put in the table name override in the appropriate
     * location if needed
     * @param  string  $modelFileContents The contents of the model file
     * @param  string  $tableName         The table name override or blank
     * @return string                     The updated model file contents
     */
    private function addTableNameIfNeeded($modelFileContents, $tableName)
    {
        // If the model has a table name, it means that it was overriden,
        // so put in a table name line
        if ($tableName)
        {
            return str_replace('{{tableName}}',
                "protected \$table = '{$tableName}';",
                $modelFileContents
            );
        }

        // Else remove the line
        else
        {
            return str_replace("    {{tableName}}\n", '', $modelFileContents);
        }
    }


    /**
     * Given a function name and all the data related to it, generate the relation
     * function block with all the necessary parameters
     * @param  string $functionName The name of the function
     * @param  array  $functionData All the meta data related to the function
     * @return string               The relation function block
     */
    private function getRelationFunction($functionName, $functionData)
    {
        // Store the template locally
        $result = $this->relationalFunctionTemplate;

        // Add in the function name
        $result = str_replace('{{functionName}}', $functionName, $result);

        // If the relation type if mt, then the function has no parameters
        // So just whip it up here and return, since this is the only odd one
        // out
        if ($functionData['relationType'] == 'mt')
        {
            return str_replace(
                '{{functionBody}}',
                'return $this->morphTo();',
                $result
            );
        }

        // Create the function body
        // We begin with:
        // return $this->function('Model'
        $functionBody = 'return $this->'
            . $this->getFunctionNameFromRelationType($functionData['relationType'])
            . "('" . $functionData['toModel'] . "'" ;

        // Add in any extra parameters
        // For belongs to many, we have the table name override first, and then
        // the foreign keys. For everything else, there is just one foreign key
        //
        // We'll arrive at one of the following:
        // return $this->function('Model', 'foreignKey'
        // return $this->function('Model', 'pivotTable'
        // return $this->function('Model', 'pivotTable', 'foreignKey1', 'foreignKey2'
        //
        // First, check if it is a belongsToMany (btm or btmc)
        if (in_array($functionData['relationType'], array('btm','btmc')))
        {
            // Check if a pivot table is provided
            if ($functionData['pivotTable'])
            {
                // Add the pivot table first
                $functionBody .= ", '" . $functionData['pivotTable'] . "'";

                // Now check if we also have the two foreign keys
                if ($functionData['foreignKey'])
                {
                    // Add the two foreign keys as well
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


        // Close the parenthesis
        $functionBody .= ')';

        // Check if the relation is btmc, and has additional fields that should
        // be added
        if (isset($functionData['additional']['btmcColumns'])
            and $functionData['additional']['btmcColumns'])
        {
            $functionBody .= "->withPivot('"
                . implode("', '", $functionData['additional']['btmcColumns'])
                . "')";
        }

        // If the relation is btmc and the migration has timestamps enabled, add
        // the withTimestamps caluse
        if (isset($functionData['additional']['btmcTimestamps'])
            and $functionData['additional']['btmcTimestamps'])
        {
            $functionBody .= '->withTimestamps()';
        }

        // Add a semicolon
        $functionBody .= ';';

        // Add the function body to the function template
        $result = str_replace('{{functionBody}}', $functionBody, $result);


        // Return the final function block
        return $result;
    }


    /**
     * Given a relation type code, get the function name as it goes inside
     * Eloquent
     * @param  string $relationType The relation type code
     * @return string               The function name of the relation as in Eloquent
     */
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
            case 'btmc':
                return 'belongsToMany';
        }
    }


    /**
     * Add the given function block at the appropriate location of the model file
     * template
     * @param  string $modelFileContents The model file contents
     * @param  string $functionBlock     The generated function block
     * @return string                    The new model file contents
     */
    private function addRelationFunction($modelFileContents, $functionBlock)
    {
        return str_replace('    {{relationalFunctions}}',
            "{$functionBlock}\n    {{relationalFunctions}}",
            $modelFileContents
        );
    }


    /**
     * Remove the relation function placeholder tag from the model contents
     * @param  string $modelFileContents The model file contents
     * @return string                    The new model file contents
     */
    private function removeRelationFunctionTag($modelFileContents)
    {
        return str_replace("\n" . '    {{relationalFunctions}}',
            '',
            $modelFileContents
        );
    }
}