<?php

use LarryFour\Parser\ModelDefinitionParser;
include SRC_ROOT . 'Parser/ModelDefinitionParser.php';

class ModelDefinitionParserTest extends PHPUnit_Framework_TestCase
{
    public function testParsingModelName()
    {
        $parsed = $this->getParsedResults("User");

        $this->assertEquals('User', $parsed['modelName']);
    }

    public function testParsingModelTableNameOverride()
    {
        $parsed = $this->getParsedResults("User users");

        $this->assertEquals('User', $parsed['modelName']);
        $this->assertEquals('users', $parsed['tableName']);
    }

    public function testParsingModelRelations()
    {
        $parsed = $this->getParsedResults("User users; hm Post;");

        $this->assertEquals('User', $parsed['modelName']);
        $this->assertEquals('users', $parsed['tableName']);

        $this->assertArrayHasKey('relations', $parsed);
        $this->assertEquals(array(
            'relatedModel' => 'Post',
            'relationType' => 'hm',
            'foreignKey' => '',
            'pivotTable' => ''
        ), $parsed['relations'][0]);
    }

    public function testParsingModelRelationForeignKeyOverride()
    {
        $parsed = $this->getParsedResults("User users; hm Post users_id;");

        $this->assertEquals(array(
            'relatedModel' => 'Post',
            'relationType' => 'hm',
            'foreignKey' => 'users_id',
            'pivotTable' => ''
        ), $parsed['relations'][0]);
    }

    public function testModelRelationsOverridesForBelongsToMany()
    {
        $parsed = $this->getParsedResults("User users; hm Post users_id; btm Group groups_users user_id group_id;");

        $this->assertEquals(array(
            'relatedModel' => 'Group',
            'relationType' => 'btm',
            'foreignKey' => array('user_id', 'group_id'),
            'pivotTable' => 'groups_users'
        ), $parsed['relations'][1]);
    }

    private function getParsedResults($line)
    {
        $m = new ModelDefinitionParser();
        return $m->parse($line);
    }
}