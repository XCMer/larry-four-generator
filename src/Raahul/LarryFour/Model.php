<?php namespace Raahul\LarryFour;

use \Illuminate\Support\Pluralizer;

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
     * The softDeletes parameter which is false by default
     * @var boolean
     */
    public $softDeletes = false;

    /**
     * The primary key column name that is an integer auto increment. It is
     * called 'id' by default.
     * @var string
     */
    public $primaryKey = 'id';

    /**
     * List of functions in a model indexed as 'function_name' =>
     * array('toModel' => , 'type' =>, 'foreignKey' =>, 'pivotTable' =>)
     * @var array
     */
    private $functions = array();


    public function __construct($modelName, $tableName)
    {
        // Set the model name
        $this->modelName = $modelName;

        // Set the table name
        $this->tableName = $tableName;
    }


    /**
     * Returns all the functions in this model as an array data structure
     * @return array Functions and info about them that have to go in this model
     */
    public function all()
    {
        return $this->functions;
    }


    /**
     * Adds a new relational function to the model
     * @param string $toModel      The related model
     * @param string $relationType The type of the relation
     * @param string $foreignKey   The foreign key override for the relation
     * @param string $pivotTable   The pivot table name override for btm
     * @param array  $additional   Key-value pairs of any additional parameters
     */
    public function addFunction($toModel, $relationType, $foreignKey, $pivotTable = '', $additional = array())
    {
        // Get the function name for the relation
        // For morphTo, the function is the same name as the foreignKey
        if ($relationType == 'mt')
        {
            $functionName = $foreignKey;
        }
        else
        {
            $functionName = $this->getRelationalFunctionName($toModel, $relationType);
        }

        // A single function can get defined twice due to hm and the corresponding
        // bt. So we'll create a new array only if the function does not already exist
        // Add in the data of the model and relationship type
        if ( !isset($this->functions[ $functionName ]) )
        {
            $this->functions[ $functionName ] = array(
                'toModel' => $toModel,
                'relationType' => $relationType,
                'foreignKey' => $foreignKey,
                'pivotTable' => $pivotTable,
                'additional' => $additional
            );
        }

        // Foreign keys can be set in either the first or the second definition, so
        // we'll update it only if we get a non-blank foreign key override
        if ($foreignKey)
        {
            $this->functions[ $functionName ]['foreignKey'] = $foreignKey;
        }
    }


    /**
     * Checks if a function exists with the given parameter in this model
     * @param  string  $functionName The name of the function
     * @param  string  $relatedModel The related model
     * @param  string  $relationType The relation type
     * @param  string  $foreignKey   The foreign key override used in the relational function
     * @param  string  $pivotTable   The pivot table name override which is only considered for btm
     * @return boolean               Whether the function given in this form exists
     */
    public function hasFunction($functionName, $relatedModel, $relationType, $foreignKey = '', $pivotTable = '')
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

        // Check for foreign key override
        if ($function['foreignKey'] != $foreignKey)
            return false;

        // Check for pivot table override in case of btm
        if ( ($function['relationType'] == 'btm')
             && ($function['pivotTable'] != $pivotTable) )
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
        // If the relation type is hasMany, hasOne, hasManyAndBelongsToMany,
        // then the function name should be the pluralized version of the related model
        if (in_array( $relationType, array('hm', 'ho', 'btm', 'btmc', 'mm') ))
        {
            return Pluralizer::plural(strtolower($toModel));
        }

        // If the relation is belongsTo, then the name of the function is the singular
        // version of the related table
        else if (in_array($relationType, array('bt', 'mo') ))
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