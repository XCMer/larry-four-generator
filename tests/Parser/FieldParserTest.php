<?php

use \LarryFour\Parser\FieldParser;
include SRC_ROOT . 'Parser/FieldParser.php';

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

    private function getParsedResults($input)
    {
        $f = new FieldParser();
        return $f->parse($input);
    }
}