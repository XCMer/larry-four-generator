<?php

use \Raahul\LarryFour\Parser\FieldParser;

class FieldParserTest extends PHPUnit_Framework_TestCase
{
    public function testClassInstantiation()
    {
        new FieldParser();
    }

    public function testIncrements()
    {
        $parsed = $this->getParsedResults("id increments");

        $this->assertEquals('id', $parsed['name']);
        $this->assertEquals('increments', $parsed['type']);
    }

    public function testTimestamps()
    {
        $parsed = $this->getParsedResults("timestamps");

        $this->assertEquals('timestamps', $parsed['type']);
    }

    public function testSoftDeletes()
    {
        $parsed = $this->getParsedResults("softDeletes");

        $this->assertEquals('softDeletes', $parsed['type']);
    }

    public function testParameterizedField()
    {
        $parsed = $this->getParsedResults("username string 50");

        $this->assertEquals('username', $parsed['name']);
        $this->assertEquals('string', $parsed['type']);
        $this->assertEquals(array(50), $parsed['parameters']);
    }

    public function testEnumField()
    {
        $parsed = $this->getParsedResults("type enum admin, moderator, user");

        $this->assertEquals('type', $parsed['name']);
        $this->assertEquals('enum', $parsed['type']);
        $this->assertEquals(array('admin', 'moderator', 'user'), $parsed['parameters']);
    }

    public function testEnumFieldWithQuotes()
    {
        $parsed = $this->getParsedResults('type enum admin, moderator, "user", "hello "');

        $this->assertEquals('type', $parsed['name']);
        $this->assertEquals('enum', $parsed['type']);
        $this->assertEquals(array('admin', 'moderator', 'user', 'hello '), $parsed['parameters']);
    }

    public function testGetLineSegments()
    {
        $f = new FieldParser();
        $output = $f->getLineSegments('username string 50; default "hello world"; nullable;');
        $expected = array(
            'username string 50',
            'default "hello world"',
            'nullable'
        );

        $this->assertEquals($expected, $output);
    }

    public function testGetLineSegmentsWithSemicolonInData()
    {
        $f = new FieldParser();
        $output = $f->getLineSegments('username string 50; default "hello; world"; nullable;');
        $expected = array(
            'username string 50',
            'default "hello; world"',
            'nullable'
        );

        $this->assertEquals($expected, $output);
    }

    public function testFieldDefaultValue()
    {
        $parsed = $this->getParsedResults('username string 50; default "hello; world"; nullable;');

        $this->assertArrayHasKey('default', $parsed);
        $this->assertEquals('hello; world', $parsed['default']);
    }

    public function testFieldNullable()
    {
        $parsed = $this->getParsedResults('username string 50; default "hello; world"; nullable;');

        $this->assertArrayHasKey('nullable', $parsed);
        $this->assertEquals(true, $parsed['nullable']);
    }

    public function testFieldUnsigned()
    {
        $parsed = $this->getParsedResults('username string 50; default "hello; world"; unsigned;');

        $this->assertArrayHasKey('unsigned', $parsed);
        $this->assertEquals(true, $parsed['unsigned']);
    }

    public function testFieldPrimary()
    {
        $parsed = $this->getParsedResults('username string 50; default "hello; world"; primary;');

        $this->assertArrayHasKey('primary', $parsed);
        $this->assertEquals(true, $parsed['primary']);
    }

    public function testFieldUnique()
    {
        $parsed = $this->getParsedResults('username string 50; default "hello; world"; unique;');

        $this->assertArrayHasKey('unique', $parsed);
        $this->assertEquals(true, $parsed['unique']);
    }

    public function testFieldFulltext()
    {
        $parsed = $this->getParsedResults('username string 50; default "hello; world"; fulltext;');

        $this->assertArrayHasKey('fulltext', $parsed);
        $this->assertEquals(true, $parsed['fulltext']);
    }

    public function testFieldNormalIndex()
    {
        $parsed = $this->getParsedResults('username string 50; default "hello; world"; index;');

        $this->assertArrayHasKey('index', $parsed);
        $this->assertEquals(true, $parsed['index']);
    }

    private function getParsedResults($input)
    {
        $f = new FieldParser();
        return $f->parse($input);
    }
}