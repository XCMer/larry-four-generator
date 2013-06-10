<?php namespace Raahul\LarryFour;

use \Illuminate\Support\Pluralizer;

class Migration
{
    /**
     * The table name for this migration
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
     * Stores a list of columns indexed by their name, referencing an array of
     * information about them
     * @var array
     */
    private $columns = array();


    public function __construct($modelName, $tableName)
    {
        // Set the given table name, or plularize the model name if
        // not given
        if (!$tableName)
        {
            $this->tableName = strtolower(Pluralizer::plural($modelName));
        }
        else
        {
            $this->tableName = $tableName;
        }
    }


    /**
     * Adds a new column to the migration
     * @param array $fieldData The parsed field data
     */
    public function addColumn($fieldData)
    {
        $this->columns[$fieldData['name']] = $fieldData;
    }


    /**
     * Returns all the columns of this migration in order
     * @return array All the columns in this migration
     */
    public function all()
    {
        return $this->columns;
    }


    /**
     * Checks if a column exists
     * @param  string $column The column name
     * @return bool           Boolean whether the column exists
     */
    public function columnExists($column)
    {
        return isset($this->columns[$column]);
    }


    /**
     * Returns the type of the column
     * @param  string $column The column name
     * @return string         The type of the column as a string
     */
    public function getColumnType($column)
    {
        return $this->columns[$column]['type'];
    }


    /**
     * Returns the additional parameters to a column
     * @param  string $column The column name
     * @return array          The additional parameters of a column
     */
    public function getColumnParameters($column)
    {
        return $this->columns[$column]['parameters'];
    }


    /**
     * The default value of a column
     * @param  string $column The column name
     * @return string         The default value of a column
     */
    public function getColumnDefault($column)
    {
        return isset($this->columns[$column]['default'])
            ? $this->columns[$column]['default']
            : '';
    }


    /**
     * Returns if the column is nullable
     * @param  string $column The column name
     * @return boolean        Boolean whether the column is nullable
     */
    public function isColumnNullable($column)
    {
        return isset($this->columns[$column]['nullable'])
            && $this->columns[$column]['nullable'];
    }


    /**
     * Returns whether the column is unsigned
     * @param  string $column The column name
     * @return boolean        Boolean whether the column is unsigned
     */
    public function isColumnUnsigned($column)
    {
        return isset($this->columns[$column]['unsigned'])
            && $this->columns[$column]['unsigned'];
    }
}