<?php

use \Raahul\LarryFour\Parser\FieldParser;
use \Raahul\LarryFour\Parser\ModelDefinitionParser;
use \Raahul\LarryFour\Parser;
use \Raahul\LarryFour\ModelList;
use \Raahul\LarryFour\MigrationList;
use \Raahul\LarryFour\Tests\ParsedResult;

class ParserTest extends PHPUnit_Framework_TestCase
{

    public function testParsingOfModelNames()
    {
        $parsed = ParsedResult::getSampleParsedObject();
        $models = $parsed['modelList']->all();

        $this->assertEquals(
            array(
                'User',
                'Post',
                'Image',
                'Role',
                'Stuff',
                'Thumb',
                'Comment'
            ),
            array_keys($models)
        );
        $this->assertInstanceOf('\Raahul\LarryFour\Model', $models['User']);
        $this->assertInstanceOf('\Raahul\LarryFour\Model', $models['Post']);
        $this->assertInstanceOf('\Raahul\LarryFour\Model', $models['Image']);
    }

    public function testPresenceOfMigrations()
    {
        $parsed = ParsedResult::getSampleParsedObject();
        $migrations = $parsed['migrationList']->all();

        $this->assertEquals(
            array(
                'User',
                'Post',
                'Image',
                'Role',
                'Stuff',
                'Thumb',
                'Comment',
                'my_great_table',
                'comment_post',
                'role_user',
                't_u'
            ),
            array_keys($migrations)
        );
    }

    public function testFieldsInMigrationOfCustomTable()
    {
        $parsed = ParsedResult::getSampleParsedObject();
        $migrations = $parsed['migrationList']->all();

        $customGreatTable = $migrations['my_great_table'];

        $this->assertTrue($customGreatTable->timestamps);
        $this->assertTrue($customGreatTable->softDeletes);
        $this->assertEquals('pK', $customGreatTable->primaryKey);

        $this->assertTrue($customGreatTable->columnExists('title'));
        $this->assertTrue($customGreatTable->columnExists('content'));
        $this->assertTrue($customGreatTable->columnExists('rating'));
    }

    public function testParsingOfModelTableNameOverrides()
    {
        $parsed = ParsedResult::getSampleParsedObject();
        $models = $parsed['modelList']->all();

        $this->assertEquals('users', $models['User']->tableName);
        $this->assertEquals('', $models['Post']->tableName);
        $this->assertEquals('', $models['Image']->tableName);
    }

    public function testParsingOfMigrationInformation()
    {
        $parsed = ParsedResult::getSampleParsedObject();
        $migrations = $parsed['migrationList']->all();

        $this->assertEquals('users', $migrations['User']->tableName);
        $this->assertEquals('posts', $migrations['Post']->tableName);
        $this->assertEquals('images', $migrations['Image']->tableName);
    }

    public function testTimestampsParameter()
    {
        $parsed = ParsedResult::getSampleParsedObject();
        $models = $parsed['modelList']->all();
        $migrations = $parsed['migrationList']->all();

        $this->assertEquals(false, $models['User']->timestamps);
        $this->assertEquals(true, $models['Post']->timestamps);
        $this->assertEquals(true, $models['Image']->timestamps);

        $this->assertEquals(false, $migrations['User']->timestamps);
        $this->assertEquals(true, $migrations['Post']->timestamps);
        $this->assertEquals(true, $migrations['Image']->timestamps);
    }

    public function testAdditionOfFieldsToMigration()
    {
        $parsed = ParsedResult::getSampleParsedObject();
        $migrations = $parsed['migrationList']->all();

        $user = $migrations['User'];
        $post = $migrations['Post'];
        $image = $migrations['Image'];

        // Check for all fields in the user table
        $this->assertEquals('id', $user->primaryKey);

        $this->assertTrue($user->columnExists('username'));
        $this->assertEquals('string', $user->getColumnType('username'));
        $this->assertEquals(array(50), $user->getColumnParameters('username'));
        $this->assertEquals("hello world", $user->getColumnDefault('username'));
        $this->assertTrue($user->isColumnNullable('username'));

        $this->assertTrue($user->columnExists('password'));
        $this->assertEquals('string', $user->getColumnType('password'));
        $this->assertEquals(array(64), $user->getColumnParameters('password'));
        $this->assertEquals("", $user->getColumnDefault('password'));
        $this->assertFalse($user->isColumnNullable('password'));

        $this->assertTrue($user->columnExists('email'));
        $this->assertEquals('string', $user->getColumnType('email'));
        $this->assertEquals(array(250), $user->getColumnParameters('email'));
        $this->assertEquals("", $user->getColumnDefault('email'));
        $this->assertFalse($user->isColumnNullable('email'));

        $this->assertTrue($user->columnExists('type'));
        $this->assertEquals('enum', $user->getColumnType('type'));
        $this->assertEquals(array('admin', 'moderator', 'user'), $user->getColumnParameters('type'));
        $this->assertEquals("", $user->getColumnDefault('type'));
        $this->assertFalse($user->isColumnNullable('type'));

    }

    public function testAdditionOfRelationFieldsToMigration()
    {
        $parsed = ParsedResult::getSampleParsedObject();
        $migrations = $parsed['migrationList']->all();

        $user = $migrations['User'];
        $post = $migrations['Post'];
        $image = $migrations['Image'];

        // Test presence of related fields
        $this->assertTrue($post->columnExists('user_id'));
        $this->assertTrue($image->columnExists('imageable_id'));
        $this->assertTrue($image->columnExists('imageable_type'));
    }

    public function testBtmIntermediaTableInMigration()
    {
        $parsed = ParsedResult::getSampleParsedObject();
        $migrations = $parsed['migrationList']->all();

        // The "model name" for the pivot table is simply the table name, with
        // the entire name lowercase (as opposed to a model)
        $role_user = $migrations['role_user'];

        // Test presence of fields
        $this->assertTrue($role_user->columnExists('role_id'));
        $this->assertTrue($role_user->columnExists('user_id'));
    }

    public function testBtmIntermediaTableInMigrationWithOverrides()
    {
        $parsed = ParsedResult::getSampleParsedObject();
        $migrations = $parsed['migrationList']->all();

        // The "model name" for the pivot table is simply the table name, with
        // the entire name lowercase (as opposed to a model)
        // In this case, it is overriden
        $t_u = $migrations['t_u'];

        // Test presence of fields
        $this->assertTrue($t_u->columnExists('t_id'));
        $this->assertTrue($t_u->columnExists('u_id'));
    }

    public function testBtRelationInMigration()
    {
        $parsed = ParsedResult::getSampleParsedObject();
        $migrations = $parsed['migrationList']->all();

        $post = $migrations['Post'];

        $this->assertTrue($post->columnExists('user_id'));
    }

    public function testHasOneHasManyFunctionAddedToModel()
    {
        $parsed = ParsedResult::getSampleParsedObject();
        $models = $parsed['modelList']->all();

        $user = $models['User'];
        $post = $models['Post'];

        $this->assertTrue($user->hasFunction('posts', 'Post', 'hm'));
        $this->assertTrue($post->hasFunction('user', 'User', 'bt'));
    }

    public function testForeignKeyOverrideInHasOneHasManyAndBelongsTo()
    {
        $parsed = ParsedResult::getSampleParsedObject();
        $models = $parsed['modelList']->all();

        $user = $models['User'];
        $stuff = $models['Stuff'];

        $this->assertTrue($stuff->hasFunction('user', 'User', 'bt', 'stuffer_id'));
        $this->assertTrue($user->hasFunction('stuffs', 'Stuff', 'hm', 'stuffer_id'));
    }

    public function testHasManyAndBelongsToModelCreationWithOverrides()
    {
        $parsed = ParsedResult::getSampleParsedObject();
        $models = $parsed['modelList']->all();

        $user = $models['User'];

        $this->assertTrue($user->hasFunction('thumbs', 'Thumb', 'btm', array('u_id', 't_id'), 't_u' ));
    }

    public function testPolymorphicRelation()
    {
        $parsed = ParsedResult::getSampleParsedObject();
        $models = $parsed['modelList']->all();

        $user = $models['User'];
        $post = $models['Post'];
        $image = $models['Image'];

        $this->assertTrue($user->hasFunction('images', 'Image', 'mm', 'imageable' ));
        $this->assertTrue($post->hasFunction('images', 'Image', 'mm', 'imageable' ));
        $this->assertTrue($image->hasFunction('imageable', null, 'mt', 'imageable'));
    }
}