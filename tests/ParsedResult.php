<?php namespace LarryFour\Tests;

use \LarryFour\Parser;
use \LarryFour\Parser\FieldParser;
use \LarryFour\Parser\ModelDefinitionParser;
use \LarryFour\ModelList;
use \LarryFour\MigrationList;
use \LarryFour\Generator\MigrationGenerator;

/**
 * This is a helper class to get the parsed result of a sample input. It's in
 * a separate class so that it can be used by different test classes
 */
class ParsedResult
{
    /**
     * The parsed object is just created and stored once for all later use
     * @var array
     */
    private static $parsed = null;


    public static function getSampleParsedObject()
    {
        if (is_null(self::$parsed))
        {
            self::$parsed = self::getParsedOutput(self::getSampleInput());
        }

        return self::$parsed;
    }


    public static function getParsedOutput($input)
    {
        $p = new Parser(
            new FieldParser(),
            new ModelDefinitionParser(),
            new ModelList(),
            new MigrationList());
        return $p->parse($input);
    }


    public static function getSampleInput()
    {
        return <<<EOF
User users; hm Post; btm Role; mm Image imageable; hm Stuff stuffer_id; btm Thumb t_u u_id t_id;
    id increments
    username string 50; default "hello world"; nullable;
    password string 64
    email string 250
    type enum admin, moderator, user

Post; mm Image imageable;
    timestamps
    title string 250
    content text
    rating decimal 5 2

Image
    timestamps

Role
    timestamps

Stuff;
    timestamps

Thumb
    timestamps
EOF;
    }
}