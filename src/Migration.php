<?php namespace LarryFour;

class Migration
{
    /**
     * The table name for this migration
     * @var string
     */
    public $tableName;


    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }
}