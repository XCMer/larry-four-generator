<?php

use \Raahul\LarryFour\ModelList;
use \Raahul\LarryFour\Generator\ModelGenerator;
use \Raahul\LarryFour\Tests\ParsedResult;

class ModelGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * An instance of the model generator so that it need not be created
     * again and again
     * @var \Raahul\LarryFour\Generator\ModelGenerator
     */
    private $modelGenerator = null;


    public function testUserModelFile()
    {
        $this->runGeneratedModelTest('User', 'model_user');
    }

    public function testPostModelFile()
    {
        $this->runGeneratedModelTest('Post', 'model_post');
    }

    public function testImageModelFile()
    {
        $this->runGeneratedModelTest('Image', 'model_image');
    }

    public function testRoleModelFile()
    {
        $this->runGeneratedModelTest('Role', 'model_role');
    }

    public function testStuffModelFile()
    {
        $this->runGeneratedModelTest('Stuff', 'model_stuff');
    }

    public function testThumbModelFile()
    {
        $this->runGeneratedModelTest('Thumb', 'model_thumb');
    }

    private function runGeneratedModelTest($modelName, $modelFile)
    {
        $expected = file_get_contents(__DIR__ . '/model_data/' . $modelFile);

        $parsed = ParsedResult::getSampleParsedObject();
        $models = $parsed['modelList']->all();
        $model = $models[$modelName];

        if (is_null($this->modelGenerator))
        {
            $this->modelGenerator = new ModelGenerator();
        }

        $this->assertEquals($expected, $this->modelGenerator->generate($model));
    }
}