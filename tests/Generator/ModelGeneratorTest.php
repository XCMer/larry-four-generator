<?php

use \LarryFour\ModelList;
use \LarryFour\Generator\ModelGenerator;
use \LarryFour\Tests\ParsedResult;

class ModelGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * An instance of the model generator so that it need not be created
     * again and again
     * @var \LarryFour\Generator\ModelGenerator
     */
    private $modelGenerator = null;


    public function testUserModelFile()
    {
        $this->runGeneratedModelTest('User', 'model_user');
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