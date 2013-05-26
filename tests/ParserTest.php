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