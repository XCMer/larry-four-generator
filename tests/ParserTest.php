<?php

use \LarryFour\Parser\FieldParser;
use \LarryFour\Parser\ModelDefinitionParser;
use \LarryFour\Parser;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testParsingOfModelNames()
    {
        $parsed = $this->getParsedOutput($this->getSampleInput());

        $this->assertArrayHasKey('models', $parsed);
        $this->assertEquals(
            array(
                'User',
                'Post',
                'Image'
            ),
            array_keys($parsed['models'])
        );
        $this->assertInstanceOf('\LarryFour\Model', $parsed['models']['User']);
        $this->assertInstanceOf('\LarryFour\Model', $parsed['models']['Post']);
        $this->assertInstanceOf('\LarryFour\Model', $parsed['models']['Image']);
    }

    public function testParsingOfModelTableNameOverrides()
    {
        $parsed = $this->getParsedOutput($this->getSampleInput());

        $this->assertEquals('users', $parsed['models']['User']->tableName);
        $this->assertEquals('posts', $parsed['models']['Post']->tableName);
        $this->assertEquals('images', $parsed['models']['Image']->tableName);
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
    timestamps
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