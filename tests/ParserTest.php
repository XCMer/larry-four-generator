<?php

use \LarryFour\Parser\FieldParser;
use \LarryFour\Parser\ModelDefinitionParser;
use \LarryFour\Parser;
use \LarryFour\ModelList;
use \LarryFour\MigrationList;

class ParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * Stores the parsed result of the sample input, since all the function parse
     * the same input
     */
    private $parsed = null;

    public function testParsingOfModelNames()
    {
        $parsed = $this->getSampleParsedObject();
        $models = $parsed['modelList']->all();

        $this->assertEquals(
            array(
                'User',
                'Post',
                'Image',
                'Role'
            ),
            array_keys($models)
        );
        $this->assertInstanceOf('\LarryFour\Model', $models['User']);
        $this->assertInstanceOf('\LarryFour\Model', $models['Post']);
        $this->assertInstanceOf('\LarryFour\Model', $models['Image']);
    }

    public function testParsingOfModelTableNameOverrides()
    {
        $parsed = $this->getSampleParsedObject();
        $models = $parsed['modelList']->all();

        $this->assertEquals('users', $models['User']->tableName);
        $this->assertEquals('posts', $models['Post']->tableName);
        $this->assertEquals('images', $models['Image']->tableName);
    }

    public function testParsingOfMigrationInformation()
    {
        $parsed = $this->getSampleParsedObject();
        $migrations = $parsed['migrationList']->all();

        $this->assertEquals('users', $migrations['User']->tableName);
        $this->assertEquals('posts', $migrations['Post']->tableName);
        $this->assertEquals('images', $migrations['Image']->tableName);
    }

    public function testTimestampsParameter()
    {
        $parsed = $this->getSampleParsedObject();
        $models = $parsed['modelList']->all();
        $migrations = $parsed['migrationList']->all();

        $this->assertEquals(false, $models['User']->timestamps);
        $this->assertEquals(true, $models['Post']->timestamps);
        $this->assertEquals(true, $models['Image']->timestamps);

        $this->assertEquals(false, $migrations['User']->timestamps);
        $this->assertEquals(true, $migrations['Post']->timestamps);
        $this->assertEquals(true, $migrations['Image']->timestamps);
    }

    public function testAdditionOfFieldsToMigration()
    {
        $parsed = $this->getSampleParsedObject();
        $migrations = $parsed['migrationList']->all();

        $user = $migrations['User'];
        $post = $migrations['Post'];
        $image = $migrations['Image'];

        // Check for all fields in the user table
        $this->assertEquals('id', $user->primaryKey);

        $this->assertTrue($user->columnExists('username'));
        $this->assertEquals('string', $user->getColumnType('username'));
        $this->assertEquals(array(50), $user->getColumnParameters('username'));
        $this->assertEquals("hello world", $user->getColumnDefault('username'));
        $this->assertTrue($user->isColumnNullable('username'));

        $this->assertTrue($user->columnExists('password'));
        $this->assertEquals('string', $user->getColumnType('password'));
        $this->assertEquals(array(64), $user->getColumnParameters('password'));
        $this->assertEquals("", $user->getColumnDefault('password'));
        $this->assertFalse($user->isColumnNullable('password'));

        $this->assertTrue($user->columnExists('email'));
        $this->assertEquals('string', $user->getColumnType('email'));
        $this->assertEquals(array(250), $user->getColumnParameters('email'));
        $this->assertEquals("", $user->getColumnDefault('email'));
        $this->assertFalse($user->isColumnNullable('email'));

        $this->assertTrue($user->columnExists('type'));
        $this->assertEquals('enum', $user->getColumnType('type'));
        $this->assertEquals(array('admin', 'moderator', 'user'), $user->getColumnParameters('type'));
        $this->assertEquals("", $user->getColumnDefault('type'));
        $this->assertFalse($user->isColumnNullable('type'));

    }

    public function testAdditionOfRelationFieldsToMigration()
    {
        $parsed = $this->getSampleParsedObject();
        $migrations = $parsed['migrationList']->all();

        $user = $migrations['User'];
        $post = $migrations['Post'];
        $image = $migrations['Image'];

        // Test presence of related fields
        $this->assertTrue($post->columnExists('user_id'));
        $this->assertTrue($image->columnExists('imageable_id'));
        $this->assertTrue($image->columnExists('imageable_type'));
    }

    public function testBtmIntermediaTableInMigration()
    {
        $parsed = $this->getSampleParsedObject();
        $migrations = $parsed['migrationList']->all();

        // The "model name" for the pivot table is simply the table name, with
        // the entire name lowercase (as opposed to a model)
        $role_user = $migrations['role_user'];

        // Test presence of fields
        $this->assertTrue($role_user->columnExists('role_id'));
        $this->assertTrue($role_user->columnExists('user_id'));
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
User users; hm Post; btm Role;
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
EOF;
    }
}