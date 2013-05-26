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
}