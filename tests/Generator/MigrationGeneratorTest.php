<?php

use \Raahul\LarryFour\Parser;
use \Raahul\LarryFour\Parser\FieldParser;
use \Raahul\LarryFour\Parser\ModelDefinitionParser;
use \Raahul\LarryFour\ModelList;
use \Raahul\LarryFour\MigrationList;
use \Raahul\LarryFour\Generator\MigrationGenerator;
use \Raahul\LarryFour\Tests\ParsedResult;

class MigrationGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * An instance of the migration generator so that it need not be created
     * again and again
     * @var [type]
     */
    private $migrationGenerator = null;


    public function testGeneratedMigrationContentsForUserTable()
    {
        $this->runGeneratedMigrationForTable('User', 'migration_user');
    }

    public function testGeneratedMigrationContentsForPostTable()
    {
        $this->runGeneratedMigrationForTable('Post', 'migration_post');
    }

    public function testGeneratedMigrationContentsForImageTable()
    {
        $this->runGeneratedMigrationForTable('Image', 'migration_image');
    }

    public function testGeneratedMigrationContentsForRoleTable()
    {
        $this->runGeneratedMigrationForTable('Role', 'migration_role');
    }

    public function testGeneratedMigrationContentsForStuffTable()
    {
        $this->runGeneratedMigrationForTable('Stuff', 'migration_stuff');
    }

    public function testGeneratedMigrationContentsForThumbTable()
    {
        $this->runGeneratedMigrationForTable('Thumb', 'migration_thumb');
    }

    public function testGeneratedMigrationContentsForRoleUserTable()
    {
        $this->runGeneratedMigrationForTable('role_user', 'migration_role_user');
    }

    public function testGeneratedMigrationContentsForTUTable()
    {
        $this->runGeneratedMigrationForTable('t_u', 'migration_t_u');
    }

    public function testGeneratedMigrationContentsForCustomTable()
    {
        $this->runGeneratedMigrationForTable('my_great_table', 'migration_my_great_table');
    }

    private function runGeneratedMigrationForTable($modelName, $migrationFile)
    {
        $expected = file_get_contents(__DIR__ . '/data/' . $migrationFile);
        // $parsed = $this->getSampleParsedObject();
        $parsed = ParsedResult::getSampleParsedObject();
        $migrations = $parsed['migrationList']->all();
        $table = $migrations[$modelName];

        if (is_null($this->migrationGenerator))
        {
            $this->migrationGenerator = new MigrationGenerator();
        }

        $this->assertEquals($expected, $this->migrationGenerator->generate($table));
    }
}