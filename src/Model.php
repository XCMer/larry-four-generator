<?php namespace LarryFour;

class Model
{
    /**
     * The name of the model, camelcase singular
     * @var string
     */
    public $modelName;

    /**
     * The table name of the model, lowercase plural
     * @var string
     */
    public $tableName;

    /**
     * The timestamps parameter which is false by default
     * @var boolean
     */
    public $timestamps = false;

    /**
     * List of functions in a model indexed as 'function_name' => array('toModel' => , 'type' =>)
     * @var array
     */
    private $functions = array();


    public function __construct($modelName, $tableName = null)
    {
        // Set the model name
        $this->modelName = $modelName;

        // Set the given table name, or plularize the model name if
        // not given
        if (!$tableName)
        {
            $this->tableName = strtolower(\LarryFour\Inflect::pluralize($modelName));
        }
        else
        {
            $this->tableName = $tableName;
        }
    }


    /**
     * Adds a new relational function to the model
     * @param string $toModel      The related model
     * @param string $relationType The type of the relation
     */
    public function addFunction($toModel, $relationType)
    {
        $functionName = $this->getRelationalFunctionName($toModel, $relationType);
        $this->functions[ $functionName ] = array(
            'toModel' => $toModel,
            'relationType' => $relationType
        );
    }


    /**
     * Checks if a function exists with the given parameter in this model
     * @param  string  $functionName The name of the function
     * @param  string  $relatedModel The related model
     * @param  string  $relationType The relation type
     * @return boolean               [description]
     */
    public function hasFunction($functionName, $relatedModel, $relationType)
    {
        // If the function doesn't exist, return false
        if (!isset( $this->functions[ $functionName ] )) return false;

        // Store the function for easier access
        $function = $this->functions[ $functionName ];

        // Else check if the function as the expected parameters
        if ($function['toModel'] != $relatedModel)
            return false;

        if ($function['relationType'] != $relationType)
            return false;

        // Return true finally
        return true;
    }


    /**
     * Get the function name of a relation in the model based on the type of relation
     * it is
     * @param  string $toModel      The related model
     * @param  string $relationType The relation type
     * @return string               The function name to be used in the relation
     */
    private function getRelationalFunctionName($toModel, $relationType)
    {
        // If the relation type is hasMany of hasOne, then the function name should
        // be the pluralized version of the related model
        if (in_array( $relationType, array('hm', 'ho') ))
        {
            return Inflect::pluralize(strtolower($toModel));
        }

        // If the relation is belongsTo, then the name of the function is the singular
        // version of the related table
        else if ( $relationType =='bt' )
        {
            return strtolower($toModel);
        }

        // For now, return a blank string in all other cases
        else
        {
            return '';
        }
    }
}