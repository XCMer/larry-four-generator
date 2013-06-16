<?php namespace Raahul\LarryFour\Tests;

use \Raahul\LarryFour\Parser;
use \Raahul\LarryFour\Parser\FieldParser;
use \Raahul\LarryFour\Parser\ModelDefinitionParser;
use \Raahul\LarryFour\ModelList;
use \Raahul\LarryFour\MigrationList;
use \Raahul\LarryFour\Generator\MigrationGenerator;

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

Post; mm Image imageable; btmc Comment;
    timestamps
    title string 250
    content text
    rating decimal 5 2

Image
    timestamps
    softDeletes

Role
    timestamps

Stuff;
    timestamps
    softDeletes

Thumb
    timestamps

Comment

table my_great_table
    timestamps
    softDeletes
    title string 250
    content text
    rating decimal 5 2
    pK increments

table comment_post
    post_id integer; unsigned
    comment_id integer; unsigned
    name string
    type string
    timestamps
EOF;
    }
}