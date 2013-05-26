<?php

use \LarryFour\Parser\FieldParser;
use \LarryFour\Parser\ModelDefinitionParser;
use \LarryFour\Parser;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testParsingOfModelData()
    {
        $parsed = $this->getParsedOutput($this->getSampleInput());

        $this->assertArrayHasKey('models', $parsed);
        $this->assertEquals(
            array(
                'User',
                'Post'
            ),
            array_keys($parsed['models'])
        );
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
    id increments (optional)
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
EOF;
    }
}