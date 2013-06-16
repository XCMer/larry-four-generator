<?php namespace Raahul\LarryFour;

/**
 * This class is meant to be used by the parser to keep track
 * of models added. In contrast to an array, this is better because it
 * allows us to create abstract functions without messing around too much
 * with associative arrays
 */
class ModelList
{
    /**
     * A list of model object arrays indexed as model name => model object
     * @var array
     */
    private $models = array();


    /**
     * Sets the timestamps true for given model
     * @param string $modelName The name of the model
     */
    public function setTimestamps($modelName)
    {
        $this->models[$modelName]->timestamps = true;
    }


    /**
     * Sets the softDeletes true for given model
     * @param string $modelName The name of the model
     */
    public function setSoftDeletes($modelName)
    {
        $this->models[$modelName]->softDeletes = true;
    }


    /**
     * Sets the primary key for given model
     * @param string $modelName  The name of the model
     * @param string $primaryKey The primary key name
     */
    public function setPrimaryKey($modelName, $primaryKey)
    {
        $this->models[$modelName]->primaryKey = $primaryKey;
    }


    /**
     * Creates and adds a new model, given the model name and the table name
     * @param string $modelName The name of the model as parsed
     * @param string $tableName The name of the table as if overriden
     *
     * @return \Raahul\LarryFour\Model The newly created model
     */
    public function create($modelName, $tableName)
    {
        return ($this->models[$modelName] = new Model($modelName, $tableName));
    }


    /**
     * Returns an array of all the models indexed by model name present in the
     * system
     * @return array All models indexed by model name
     */
    public function all()
    {
        return $this->models;
    }


    /**
     * A wrapper for the addFunction method of a model that adds a relational
     * function to the model file
     * @param string $inModel      In which model the function has to be added
     * @param string $toModel      The related model
     * @param string $relationType The type of the relation
     * @param string $foreignKey   The foreign key override for the relation
     * @param string $pivotTable   The pivot table name override for btm
     * @param array  $additional   Key-value pairs of any additional parameters
     */
    public function addFunction($inModel, $toModel, $relationType, $foreignKey, $pivotTable = '', $additional = array())
    {
        $this->models[ $inModel ]->addFunction($toModel, $relationType, $foreignKey, $pivotTable, $additional);
    }


    /**
     * Returns whether the given model exists
     * @param  string $modelName The name of the model as parsed
     * @return bool              If the model exists
     */
    public function exists($modelName)
    {
        return isset($this->models[ $modelName ]);
    }

}