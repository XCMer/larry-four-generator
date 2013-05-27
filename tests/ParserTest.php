<?php

use \LarryFour\Parser\FieldParser;
use \LarryFour\Parser\ModelDefinitionParser;
use \LarryFour\Parser;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testParsingOfModelNames()
    {
        $parsed = $this->getParsedOutput($this->getSampleInput());

        $this->assertEquals(
            array(
                'User',
                'Post',
                'Image'
            ),
            array_keys($parsed)
        );
        $this->assertInstanceOf('\LarryFour\Model', $parsed['User']['model']);
        $this->assertInstanceOf('\LarryFour\Model', $parsed['Post']['model']);
        $this->assertInstanceOf('\LarryFour\Model', $parsed['Image']['model']);
    }

    public function testParsingOfModelTableNameOverrides()
    {
        $parsed = $this->getParsedOutput($this->getSampleInput());

        $this->assertEquals('users', $parsed['User']['model']->tableName);
        $this->assertEquals('posts', $parsed['Post']['model']->tableName);
        $this->assertEquals('images', $parsed['Image']['model']->tableName);
    }

    public function testParsingOfMigrationInformation()
    {
        $parsed = $this->getParsedOutput($this->getSampleInput());

        $this->assertEquals('users', $parsed['User']['migration']->tableName);
        $this->assertEquals('posts', $parsed['Post']['migration']->tableName);
        $this->assertEquals('images', $parsed['Image']['migration']->tableName);
    }

    public function testTimestampsParameter()
    {
        $parsed = $this->getParsedOutput($this->getSampleInput());

        $this->assertEquals(false, $parsed['User']['migration']->timestamps);
        $this->assertEquals(true, $parsed['Post']['migration']->timestamps);
        $this->assertEquals(true, $parsed['Image']['migration']->timestamps);

        $this->assertEquals(false, $parsed['User']['model']->timestamps);
        $this->assertEquals(true, $parsed['Post']['model']->timestamps);
        $this->assertEquals(true, $parsed['Image']['model']->timestamps);
    }

    public function testAdditionOfFieldsToMigration()
    {
        $parsed = $this->getParsedOutput($this->getSampleInput());

        $user = $parsed['User']['migration'];
        $post = $parsed['Post']['migration'];
        $image = $parsed['Image']['migration'];

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
        $parsed = $this->getParsedOutput($this->getSampleInput());

        $user = $parsed['User']['migration'];
        $post = $parsed['Post']['migration'];
        $image = $parsed['Image']['migration'];

        // Test presence of related fields
        $this->assertTrue($post->columnExists('user_id'));
        $this->assertTrue($image->columnExists('imageable_id'));
        $this->assertTrue($image->columnExists('imageable_type'));
    }

    private function getParsedOutput($input)
    {
        $p = new Parser(new FieldParser(), new ModelDefinitionParser());
        return $p->parse($input);
    }

    private function getSampleInput()
    {
        return <<<EOF
User users; hm Post;
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
EOF;
    }
}