<?php

use \LarryFour\Parser\FieldParser;
use \LarryFour\Parser\ModelDefinitionParser;
use \LarryFour\Parser;
use \LarryFour\ModelList;
use \LarryFour\MigrationList;
use \LarryFour\Exception\ParseError;

class ParseErrorsTest extends PHPUnit_Framework_TestCase
{
    public function testSufficientParametersForRelations()
    {
        // An additional line just for fun
        $input = <<<EOF

User users; hm Post; Role; mm Image imageable;
EOF;

        $this->assertErrorOutput($input, "[Line 2] Insufficient parameters for relation: Role\n");
    }

    public function testValidRelationTypesError()
    {
        $input = <<<EOF
User users; hms Post; btm Role; mm Image imageable;
EOF;

        $this->assertErrorOutput($input, "[Line 1] Invalid relation type: hms\n");
    }

    public function testTestMandatoryParameterForPolymorphicRelations()
    {
        $input = <<<EOF
User users; hm Post; btm Role; mm Image;
EOF;

        $this->assertErrorOutput($input, "[Line 1] Polymorphic relations require foreign key to be specified: mm Image\n");
    }

    public function testTestBtmRelationToThrowErrorOnInsufficientParameters()
    {
        $input = <<<EOF
User users; hm Post; btm Role roles_user role_id; mm Image imageable;
EOF;

        $this->assertErrorOutput($input, "[Line 1] Belongs to many relation needs none or both foreign keys present, but found just one: btm Role roles_user role_id\n");
    }

    public function testInvalidFieldTypesThrowError()
    {
        $input = <<<EOF
User users; hm Post; btm Role roles_user; mm Image imageable;
    user strings;
EOF;

        $this->assertErrorOutput($input, "[Line 2] Invalid field type: strings\n");
    }

    private function assertErrorOutput($input, $expectedError)
    {
        try {
            $this->getParsedOutput($input);
            $this->fail("Error not thrown: " . $expectedError);
        }
        catch (ParseError $e)
        {
             $this->assertEquals($expectedError, $e->getMessage());
        }
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
}