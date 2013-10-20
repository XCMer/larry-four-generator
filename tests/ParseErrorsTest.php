<?php

use \Raahul\LarryFour\Parser\FieldParser;
use \Raahul\LarryFour\Parser\ModelDefinitionParser;
use \Raahul\LarryFour\Parser;
use \Raahul\LarryFour\ModelList;
use \Raahul\LarryFour\MigrationList;
use \Raahul\LarryFour\Exception\ParseError;
use \Raahul\LarryFour\Tests\ParsedResult;

class ParseErrorsTest extends PHPUnit_Framework_TestCase
{
    public function testSufficientParametersForRelations()
    {
        // An additional line just for fun
        $input = <<<EOF

User users; hm Post; Role; mm Image imageable;
EOF;

        $this->assertErrorOutput($input, "[Line 2] Insufficient parameters for relation: Role");
    }

    public function testValidRelationTypesError()
    {
        $input = <<<EOF
User users; hms Post; btm Role; mm Image imageable;
EOF;

        $this->assertErrorOutput($input, "[Line 1] Invalid relation type: hms");
    }

    public function testTestMandatoryParameterForPolymorphicRelations()
    {
        $input = <<<EOF
User users; hm Post; btm Role; mm Image;
EOF;

        $this->assertErrorOutput($input, "[Line 1] Polymorphic relations require foreign key to be specified: mm Image");
    }

    public function testTestBtmRelationToThrowErrorOnInsufficientParameters()
    {
        $input = <<<EOF
User users; hm Post; btm Role roles_user role_id; mm Image imageable;
EOF;

        $this->assertErrorOutput($input, "[Line 1] Belongs to many relation needs none or both foreign keys present, but found just one: btm Role roles_user role_id");
    }

    public function testInvalidFieldTypesThrowError()
    {
        $input = <<<EOF
User users; hm Post; btm Role roles_user; mm Image imageable;
    user strings;
EOF;

        $this->assertErrorOutput($input, "[Line 2] Invalid field type: strings");
    }

    public function testInsufficientFieldDataThrowsError()
    {
        $input = <<<EOF
User users; hm Post; btm Role roles_user; mm Image imageable;
    user;
EOF;

        $this->assertErrorOutput($input, "[Line 2] Field does not have type provided: user");
    }

    public function testInvalidFieldModifierThrowsError()
    {
        $input = <<<EOF
User users; hm Post; btm Role roles_user; mm Image imageable;
    user string 50; nullis;
EOF;

        $this->assertErrorOutput($input, "[Line 2] Invalid field modifier: nullis");
    }

    public function testInsufficientParametersForDecimalFieldThrowsError()
    {
        $input = <<<EOF
User users; hm Post; btm Role roles_user; mm Image imageable;
    user decimal 50;
EOF;

        $this->assertErrorOutput($input, "[Line 2] Decimal field requires two parameters, precision and scale: user decimal 50");
    }

    public function testUsingBtRelationThrowsError()
    {
        $input = <<<EOF
User users; hm Post; btm Role roles_user; mm Image imageable; bt Something;
EOF;

        $this->assertErrorOutput($input, "[Line 1] Belongs to relation should not be explicitly specified in this model. Please specify a hasOne or hasMany relation in the related model \"Something\"");
    }

    public function testRelatedModelNotDefinedError()
    {
        $input = <<<EOF
User users; hm Post; ho Profile;
Post
EOF;

    $this->assertErrorOutput($input, "Model definition for model \"Profile\" not found, but relation to it is defined in model \"User\"");
    }

    public function testSegmentParsingError()
    {
        $input = <<<EOF
User:
    access enum "admin","jack","
EOF;

    $this->assertErrorOutput($input, "[Line 2] Could not parse field line. Check for errors like misplaced quotes.");
    }

    private function assertErrorOutput($input, $expectedError)
    {
        try {
            ParsedResult::getParsedOutput($input);
            $this->fail("Error not thrown: " . $expectedError);
        }
        catch (ParseError $e)
        {
             $this->assertEquals($expectedError, $e->getMessage());
        }
    }
}