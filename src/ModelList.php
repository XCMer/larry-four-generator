<?php namespace LarryFour;

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
     * Creates and adds a new model, given the model name and the table name
     * @param string $modelName The name of the model as parsed
     * @param string $tableName The name of the table as parsed
     *
     * @return \LarryFour\Model The newly created model
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
}