<?php

use \Raahul\LarryFour\MigrationList;
use \Raahul\LarryFour\DbParser;
use \Raahul\SchemaExtractor\SchemaExtractor;

class DbParserTest extends PHPUnit_Framework_TestCase
{
    private $parsedMigrationList = null;

    public function testMigrationTableName()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();

        $this->assertEquals('testings', $migrations['Testing']->tableName);
    }

    public function testIdColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertFalse($testing->columnExists('id'));
        $this->assertEquals('id', $testing->primaryKey);
    }

    public function testEmailColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('email'));
        $this->assertEquals('string', $testing->getColumnType('email'));
        $this->assertEquals(array(255), $testing->getColumnParameters('email'));
        $this->assertFalse($testing->isColumnNullable('email'));
        $this->assertFalse($testing->isColumnUnsigned('email'));
        $this->assertEquals('', $testing->getColumnDefault('email'));
    }

    public function testNameColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('name'));
        $this->assertEquals('string', $testing->getColumnType('name'));
        $this->assertEquals(array(100), $testing->getColumnParameters('name'));
        $this->assertFalse($testing->isColumnNullable('name'));
        $this->assertFalse($testing->isColumnUnsigned('name'));
        $this->assertEquals('', $testing->getColumnDefault('name'));
    }

    public function testVotesColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('votes'));
        $this->assertEquals('integer', $testing->getColumnType('votes'));
        $this->assertEquals(array(), $testing->getColumnParameters('votes'));
        $this->assertFalse($testing->isColumnNullable('votes'));
        $this->assertFalse($testing->isColumnUnsigned('votes'));
        $this->assertEquals('', $testing->getColumnDefault('votes'));
    }

    public function testVotesBigIntColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('votes_big'));
        $this->assertEquals('bigInteger', $testing->getColumnType('votes_big'));
        $this->assertEquals(array(), $testing->getColumnParameters('votes_big'));
        $this->assertFalse($testing->isColumnNullable('votes_big'));
        $this->assertFalse($testing->isColumnUnsigned('votes_big'));
        $this->assertEquals('', $testing->getColumnDefault('votes_big'));
    }

    public function testVotesSmallIntColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('votes_small'));
        $this->assertEquals('smallInteger', $testing->getColumnType('votes_small'));
        $this->assertEquals(array(), $testing->getColumnParameters('votes_small'));
        $this->assertFalse($testing->isColumnNullable('votes_small'));
        $this->assertFalse($testing->isColumnUnsigned('votes_small'));
        $this->assertEquals('', $testing->getColumnDefault('votes_small'));
    }

    public function testAmountFloatColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('amount_float'));
        $this->assertEquals('float', $testing->getColumnType('amount_float'));
        $this->assertEquals(array(), $testing->getColumnParameters('amount_float'));
        $this->assertFalse($testing->isColumnNullable('amount_float'));
        $this->assertFalse($testing->isColumnUnsigned('amount_float'));
        $this->assertEquals('', $testing->getColumnDefault('amount_float'));
    }

    public function testAmountDecimalColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('amount_decimal'));
        $this->assertEquals('decimal', $testing->getColumnType('amount_decimal'));
        $this->assertEquals(array(5, 2), $testing->getColumnParameters('amount_decimal'));
        $this->assertFalse($testing->isColumnNullable('amount_decimal'));
        $this->assertFalse($testing->isColumnUnsigned('amount_decimal'));
        $this->assertEquals('', $testing->getColumnDefault('amount_decimal'));
    }

    public function testConfirmedBooleanColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('confirmed_boolean'));
        $this->assertEquals('boolean', $testing->getColumnType('confirmed_boolean'));
        $this->assertEquals(array(), $testing->getColumnParameters('confirmed_boolean'));
        $this->assertFalse($testing->isColumnNullable('confirmed_boolean'));
        $this->assertFalse($testing->isColumnUnsigned('confirmed_boolean'));
        $this->assertEquals('', $testing->getColumnDefault('confirmed_boolean'));
    }

    public function testCreatedAtDateColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('created_at_date'));
        $this->assertEquals('date', $testing->getColumnType('created_at_date'));
        $this->assertEquals(array(), $testing->getColumnParameters('created_at_date'));
        $this->assertFalse($testing->isColumnNullable('created_at_date'));
        $this->assertFalse($testing->isColumnUnsigned('created_at_date'));
        $this->assertEquals('', $testing->getColumnDefault('created_at_date'));
    }

    public function testCreatedAtDateTimeColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('created_at_datetime'));
        $this->assertEquals('dateTime', $testing->getColumnType('created_at_datetime'));
        $this->assertEquals(array(), $testing->getColumnParameters('created_at_datetime'));
        $this->assertFalse($testing->isColumnNullable('created_at_datetime'));
        $this->assertFalse($testing->isColumnUnsigned('created_at_datetime'));
        $this->assertEquals('', $testing->getColumnDefault('created_at_datetime'));
    }

    public function testSunriseTimeColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('sunrise_time'));
        $this->assertEquals('time', $testing->getColumnType('sunrise_time'));
        $this->assertEquals(array(), $testing->getColumnParameters('sunrise_time'));
        $this->assertFalse($testing->isColumnNullable('sunrise_time'));
        $this->assertFalse($testing->isColumnUnsigned('sunrise_time'));
        $this->assertEquals('', $testing->getColumnDefault('sunrise_time'));
    }

    public function testAddedOnTimestampColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('added_on_timestamp'));
        $this->assertEquals('timestamp', $testing->getColumnType('added_on_timestamp'));
        $this->assertEquals(array(), $testing->getColumnParameters('added_on_timestamp'));
        $this->assertFalse($testing->isColumnNullable('added_on_timestamp'));
        $this->assertFalse($testing->isColumnUnsigned('added_on_timestamp'));
        $this->assertEquals('0000-00-00 00:00:00', $testing->getColumnDefault('added_on_timestamp'));
    }

    public function testCreatedAtTimestampColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('created_at'));
        $this->assertEquals('timestamp', $testing->getColumnType('created_at'));
        $this->assertEquals(array(), $testing->getColumnParameters('created_at'));
        $this->assertFalse($testing->isColumnNullable('created_at'));
        $this->assertFalse($testing->isColumnUnsigned('created_at'));
        $this->assertEquals('0000-00-00 00:00:00', $testing->getColumnDefault('created_at'));
    }

    public function testUpdatedAtTimestampColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('updated_at'));
        $this->assertEquals('timestamp', $testing->getColumnType('updated_at'));
        $this->assertEquals(array(), $testing->getColumnParameters('updated_at'));
        $this->assertFalse($testing->isColumnNullable('updated_at'));
        $this->assertFalse($testing->isColumnUnsigned('updated_at'));
        $this->assertEquals('0000-00-00 00:00:00', $testing->getColumnDefault('updated_at'));
    }

    public function testDeletedAtTimestampColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('deleted_at'));
        $this->assertEquals('timestamp', $testing->getColumnType('deleted_at'));
        $this->assertEquals(array(), $testing->getColumnParameters('deleted_at'));
        $this->assertTrue($testing->isColumnNullable('deleted_at'));
        $this->assertFalse($testing->isColumnUnsigned('deleted_at'));
        $this->assertEquals('', $testing->getColumnDefault('deleted_at'));
    }

    public function testDescriptionTextColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('description_text'));
        $this->assertEquals('text', $testing->getColumnType('description_text'));
        $this->assertEquals(array(), $testing->getColumnParameters('description_text'));
        $this->assertFalse($testing->isColumnNullable('description_text'));
        $this->assertFalse($testing->isColumnUnsigned('description_text'));
        $this->assertEquals('', $testing->getColumnDefault('description_text'));
    }

    public function testDataBinaryColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('data_binary'));
        $this->assertEquals('binary', $testing->getColumnType('data_binary'));
        $this->assertEquals(array(), $testing->getColumnParameters('data_binary'));
        $this->assertFalse($testing->isColumnNullable('data_binary'));
        $this->assertFalse($testing->isColumnUnsigned('data_binary'));
        $this->assertEquals('', $testing->getColumnDefault('data_binary'));
    }

    public function testChoicesEnumColumn()
    {
        $migrationList = $this->getParsedMigrationList();
        $migrations = $migrationList->all();
        $testing = $migrations['Testing'];

        $this->assertTrue($testing->columnExists('choices_enum'));
        $this->assertEquals('enum', $testing->getColumnType('choices_enum'));
        $this->assertEquals(array('foo', 'bar'), $testing->getColumnParameters('choices_enum'));
        $this->assertFalse($testing->isColumnNullable('choices_enum'));
        $this->assertFalse($testing->isColumnUnsigned('choices_enum'));
        $this->assertEquals('', $testing->getColumnDefault('choices_enum'));
    }

    private function getParsedMigrationList()
    {
        if (is_null($this->parsedMigrationList))
        {
            $dbParser = new DbParser(new MigrationList(), new SchemaExtractor(), 'mysql');
            $this->migrationList = $dbParser->parse(array('testings' => $this->getTableDescribeRepresentation()));
        }

        return $this->migrationList;
    }

    private function getTableDescribeRepresentation()
    {
        return unserialize('a:19:{i:0;O:8:"stdClass":6:{s:5:"Field";s:2:"id";s:4:"Type";s:16:"int(10) unsigned";s:4:"Null";s:2:"NO";s:3:"Key";s:3:"PRI";s:7:"Default";N;s:5:"Extra";s:14:"auto_increment";}i:1;O:8:"stdClass":6:{s:5:"Field";s:5:"email";s:4:"Type";s:12:"varchar(255)";s:4:"Null";s:2:"NO";s:3:"Key";s:0:"";s:7:"Default";N;s:5:"Extra";s:0:"";}i:2;O:8:"stdClass":6:{s:5:"Field";s:4:"name";s:4:"Type";s:12:"varchar(100)";s:4:"Null";s:2:"NO";s:3:"Key";s:0:"";s:7:"Default";N;s:5:"Extra";s:0:"";}i:3;O:8:"stdClass":6:{s:5:"Field";s:5:"votes";s:4:"Type";s:7:"int(11)";s:4:"Null";s:2:"NO";s:3:"Key";s:3:"UNI";s:7:"Default";N;s:5:"Extra";s:0:"";}i:4;O:8:"stdClass":6:{s:5:"Field";s:9:"votes_big";s:4:"Type";s:10:"bigint(20)";s:4:"Null";s:2:"NO";s:3:"Key";s:3:"MUL";s:7:"Default";N;s:5:"Extra";s:0:"";}i:5;O:8:"stdClass":6:{s:5:"Field";s:11:"votes_small";s:4:"Type";s:11:"smallint(6)";s:4:"Null";s:2:"NO";s:3:"Key";s:0:"";s:7:"Default";N;s:5:"Extra";s:0:"";}i:6;O:8:"stdClass":6:{s:5:"Field";s:12:"amount_float";s:4:"Type";s:10:"float(8,2)";s:4:"Null";s:2:"NO";s:3:"Key";s:0:"";s:7:"Default";N;s:5:"Extra";s:0:"";}i:7;O:8:"stdClass":6:{s:5:"Field";s:14:"amount_decimal";s:4:"Type";s:12:"decimal(5,2)";s:4:"Null";s:2:"NO";s:3:"Key";s:0:"";s:7:"Default";N;s:5:"Extra";s:0:"";}i:8;O:8:"stdClass":6:{s:5:"Field";s:17:"confirmed_boolean";s:4:"Type";s:10:"tinyint(1)";s:4:"Null";s:2:"NO";s:3:"Key";s:0:"";s:7:"Default";N;s:5:"Extra";s:0:"";}i:9;O:8:"stdClass":6:{s:5:"Field";s:15:"created_at_date";s:4:"Type";s:4:"date";s:4:"Null";s:2:"NO";s:3:"Key";s:0:"";s:7:"Default";N;s:5:"Extra";s:0:"";}i:10;O:8:"stdClass":6:{s:5:"Field";s:19:"created_at_datetime";s:4:"Type";s:8:"datetime";s:4:"Null";s:2:"NO";s:3:"Key";s:0:"";s:7:"Default";N;s:5:"Extra";s:0:"";}i:11;O:8:"stdClass":6:{s:5:"Field";s:12:"sunrise_time";s:4:"Type";s:4:"time";s:4:"Null";s:2:"NO";s:3:"Key";s:0:"";s:7:"Default";N;s:5:"Extra";s:0:"";}i:12;O:8:"stdClass":6:{s:5:"Field";s:18:"added_on_timestamp";s:4:"Type";s:9:"timestamp";s:4:"Null";s:2:"NO";s:3:"Key";s:0:"";s:7:"Default";s:19:"0000-00-00 00:00:00";s:5:"Extra";s:0:"";}i:13;O:8:"stdClass":6:{s:5:"Field";s:10:"created_at";s:4:"Type";s:9:"timestamp";s:4:"Null";s:2:"NO";s:3:"Key";s:0:"";s:7:"Default";s:19:"0000-00-00 00:00:00";s:5:"Extra";s:0:"";}i:14;O:8:"stdClass":6:{s:5:"Field";s:10:"updated_at";s:4:"Type";s:9:"timestamp";s:4:"Null";s:2:"NO";s:3:"Key";s:0:"";s:7:"Default";s:19:"0000-00-00 00:00:00";s:5:"Extra";s:0:"";}i:15;O:8:"stdClass":6:{s:5:"Field";s:10:"deleted_at";s:4:"Type";s:9:"timestamp";s:4:"Null";s:3:"YES";s:3:"Key";s:0:"";s:7:"Default";N;s:5:"Extra";s:0:"";}i:16;O:8:"stdClass":6:{s:5:"Field";s:16:"description_text";s:4:"Type";s:4:"text";s:4:"Null";s:2:"NO";s:3:"Key";s:0:"";s:7:"Default";N;s:5:"Extra";s:0:"";}i:17;O:8:"stdClass":6:{s:5:"Field";s:11:"data_binary";s:4:"Type";s:4:"blob";s:4:"Null";s:2:"NO";s:3:"Key";s:0:"";s:7:"Default";N;s:5:"Extra";s:0:"";}i:18;O:8:"stdClass":6:{s:5:"Field";s:12:"choices_enum";s:4:"Type";s:17:"enum(\'foo\',\'bar\')";s:4:"Null";s:2:"NO";s:3:"Key";s:0:"";s:7:"Default";N;s:5:"Extra";s:0:"";}}');
    }
}