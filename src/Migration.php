<?php namespace LarryFour;

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


    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }
}