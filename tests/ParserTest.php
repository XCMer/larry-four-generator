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
    }

    public function testParsingOfModelTableNameOverrides()
    {
        $parsed = $this->getParsedOutput($this->getSampleInput());

        $this->assertEquals('users', $parsed['models']['User']['tableName']);
        $this->assertEquals('', $parsed['models']['Post']['tableName']);
        $this->assertEquals('', $parsed['models']['Image']['tableName']);
    }

    public function testParsingOfRelationsBetweenModels()
    {
        $parsed = $this->getParsedOutput($this->getSampleInput());

        $this->assertArrayHasKey('relations', $parsed);
        $this->assertEquals(
            array(
                array(
                    'fromModel' => 'User',
                    'toModel' => 'Post',
                    'relationType' => 'hm',
                    'foreignKey' => '',
                    'pivotTable' => ''
                ),
                array(
                    'fromModel' => 'Post',
                    'toModel' => 'User',
                    'relationType' => 'bt',
                    'foreignKey' => '',
                    'pivotTable' => ''
                ),
                array(
                    'fromModel' => 'Post',
                    'toModel' => 'Image',
                    'relationType' => 'mm',
                    'foreignKey' => 'imageable',
                    'pivotTable' => ''
                )
            ),
            $parsed['relations']
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