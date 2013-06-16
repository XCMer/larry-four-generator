<?php namespace Raahul\LarryFour;

use \Raahul\LarryFour\Exception\ParseError;

/**
 * This class is meant to be used by the parser to keep track
 * of migrations added. In contrast to an array, this is better because it
 * allows us to create abstract functions without messing around too much
 * with associative arrays
 */
class MigrationList
{
    /**
     * A list of migration object arrays indexed as model name => migration object
     * @var array
     */
    private $migrations = array();

    /**
     * Sets the timestamps true for the table belonging to the given model
     * @param string $modelName The name of the model
     */
    public function setTimestamps($modelName)
    {
        $this->migrations[$modelName]->timestamps = true;
    }


    /**
     * Sets the softDeletes true for the table belonging to the given model
     * @param string $modelName The name of the model
     */
    public function setSoftDeletes($modelName)
    {
        $this->migrations[$modelName]->softDeletes = true;
    }


    /**
     * Sets the primary key for the table belonging to the given model
     * @param string $modelName  The name of the model
     * @param string $primaryKey The name of the primary key
     */
    public function setPrimaryKey($modelName, $primaryKey)
    {
        $this->migrations[$modelName]->primaryKey = $primaryKey;
    }


    /**
     * Adds a column to the table represented by the given model
     * @param string $modelName  The name of the model
     * @param array  $columnInfo The column data as accepted by the migration function addColumn4
     *
     * @return \Raahul\LarryFour\Migration The newly created migration
     */
    public function addColumn($modelName, $columnInfo)
    {
        return ($this->migrations[$modelName]->addColumn($columnInfo));
    }


    /**
     * A wrapper around addColumn that is tailored towards adding a foreign key
     * @param string $modelName  The name of the table's model where the fk is being added
     * @param string $fromModel  The name of the model where the relationship was specified
     * @param string $foreignKey The foreign key override which is blank string of there is no override
     * @param string $type       The type of the foreign key field
     */
    public function addForeignKey($modelName, $fromModel, $foreignKey, $type = 'integer')
    {
        // First, check if the given model exists, or else throw an error
        if (!$this->exists($modelName))
        {
            throw new ParseError("Model definition for model \"{$modelName}\" not found, but relation to it is defined in model \"{$fromModel}\"");
        }

        // The foreign key is either the one provided, or if it is blank, we'll use what
        // Laravel does by default: lowercasing the fromModel and appending "_id"
        $foreignKey = $foreignKey
                    ? $foreignKey
                    : strtolower($fromModel) . '_id';

        // Add the column
        $this->addColumn($modelName, array(
            'name' => $foreignKey,
            'type' => $type,
            'parameters' => array(),
            'unsigned' => ($type == 'integer')
        ));
    }


    /**
     * Creates and adds a new migration, given the model name and the table name
     * @param string $modelName The name of the model as parsed
     * @param string $tableName The name of the table as parsed
     */
    public function create($modelName, $tableName)
    {
        $this->migrations[$modelName] = new Migration($modelName, $tableName);
    }


    /**
     * Returns an array of all the migrations indexed by model name present in the
     * system
     * @return array All migrations indexed by model name
     */
    public function all()
    {
        return $this->migrations;
    }


    /**
     * Returns whether the given model's migration exists
     * @param  string $modelName The name of the model as parsed
     * @return bool              If the model exists
     */
    public function exists($modelName)
    {
        return isset($this->migrations[ $modelName ]);
    }


    /**
     * Returns the migration with the given model name
     * @param  string      $modelName The name of the model as parsed
     * @return Migration              The migration object
     */
    public function get($modelName)
    {
        return $this->migrations[ $modelName ];
    }

}