<?php

use \LarryFour\Parser;
use \LarryFour\Parser\FieldParser;
use \LarryFour\Parser\ModelDefinitionParser;
use \LarryFour\ModelList;
use \LarryFour\MigrationList;
use \LarryFour\Generator\MigrationGenerator;

class MigrationGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * The output array of the parsed input file
     * @var array
     */
    private $parsed = null;


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

    // public function testGeneratedMigrationContentsForRoleUserTable()
    // {
    //     $this->runGeneratedMigrationForTable('role_user', 'migration_role_user');
    // }

    // public function testGeneratedMigrationContentsForTUTable()
    // {
    //     $this->runGeneratedMigrationForTable('t_u', 'migration_t_u');
    // }

    private function runGeneratedMigrationForTable($modelName, $migrationFile)
    {
        $expected = file_get_contents(__DIR__ . '/data/' . $migrationFile);
        $parsed = $this->getSampleParsedObject();
        $migrations = $parsed['migrationList']->all();
        $table = $migrations[$modelName];

        if (is_null($this->migrationGenerator))
        {
            $this->migrationGenerator = new MigrationGenerator();
        }

        $this->assertEquals($expected, $this->migrationGenerator->generate($table));
    }

    private function getSampleParsedObject()
    {
        if (is_null($this->parsed))
        {
            $this->parsed = $this->getParsedOutput($this->getSampleInput());
        }

        return $this->parsed;
    }

    private function getParsedOutput($input)
    {
        $p = new Parser(
            new FieldParser(),
            new ModelDefinitionParser(),
            new ModelList(),
            new MigrationList());
        return $p->parse($input);
    }

    private function getSampleInput()
    {
        return <<<EOF
User users; hm Post; btm Role; mm Image imageable; hm Stuff stuffer_id; btm Thumb t_u u_id t_id;
    id increments
    username string 50; default "hello world"; nullable;
    password string 64
    email string 250
    type enum admin, moderator, user

Post; bt User; mm Image imageable;
    timestamps
    title string 250
    content text
    rating decimal 5 2

Image
    timestamps

Role
    timestamps

Stuff; bt User;
    timestamps

Thumb
    timestamps
EOF;
    }
}