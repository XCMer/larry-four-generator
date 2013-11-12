<?php

use \Raahul\LarryFour\ModelList;
use \Raahul\LarryFour\Generator\MigrationGenerator;
use \Raahul\LarryFour\Generator\ModelGenerator;
use \Raahul\LarryFour\Tests\ParsedResult;
use \Mockery as m;

class ModelGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * An instance of the model generator so that it need not be created
     * again and again
     * @var \Raahul\LarryFour\Generator\ModelGenerator
     */
    private $modelGenerator = null;
    
    /**
     * An instance of the migration generator so that it need not be created
     * again and again
     * @var \Raahul\LarryFour\Generator\MigrationGenerator
     */
    
    private $migrationGenerator = null;

    
    public function setUp()
    {
        parent::setUp();

        $app = m::mock('AppMock');
        $app->shouldReceive('instance')->once()->andReturn($app);

        $config = m::mock('ConfigMock');
        \Illuminate\Support\Facades\Facade::setFacadeApplication($app);
        \Illuminate\Support\Facades\Config::swap($config);
        
        //$config->shouldReceive('get')->once()->with('larryfour::validation.processModels')->andReturn(false);
        $config->shouldReceive('get')->once()->with('larryfour::validation.processSelection.items',array())->andReturn(array());
        $config->shouldReceive('get')->once()->with('larryfour::validation.processSelection.function')->andReturn('except');
        $config->shouldReceive('get')->once()->with('larryfour::validation.processModels',false)->andReturn(false);
        $config->shouldReceive('get')->once()->with('larryfour::validation.defaults.parentClass')->andReturn('Eloquent');
        
        
        $config->shouldReceive('get')->once()->with('larryfour::slugs.selectSlugs.items',array())->andReturn(array());
        $config->shouldReceive('get')->once()->with('larryfour::slugs.selectSlugs.function','except')->andReturn('except');
        
        $config->shouldReceive('get')->once()->with('larryfour::slugs.configureSlugs',array())->andReturn(array(
        'default' => array(
            'required' => array('title','slug'),
            'forbidden' => array(),
            'rules' => array(
                'build_from' => 'title',
                'save_to'    => 'slug',
                'method'     => null,
                'separator'  => '-',
                'unique'     => true,
                'on_update'  => false,
            ),
            'builder' => array(
              'createGetters' => true,
              'mapGetters' => array(
                  array('methodName'=>'Title','fieldName'=>'title'),
                  ),
            ))));
        
        $config->shouldReceive('get')->once()->with('larryfour::slugs.createSlugs',false)->andReturn(false);
    }

    protected function tearDown() 
    {
         m::close();
    }

    public function testUserModelFile()
    {
        $this->runGeneratedModelTest('User', 'model_user','migration_user');
    }

    public function testPostModelFile()
    {
        $this->runGeneratedModelTest('Post', 'model_post','migration_user');
    }

    public function testImageModelFile()
    {
        $this->runGeneratedModelTest('Image', 'model_image','migration_image');
    }

    public function testRoleModelFile()
    {
        $this->runGeneratedModelTest('Role', 'model_role','migration_role');
    }

    public function testStuffModelFile()
    {
        $this->runGeneratedModelTest('Stuff', 'model_stuff','migration_stuff');
    }

    public function testThumbModelFile()
    {
        $this->runGeneratedModelTest('Thumb', 'model_thumb','migration_thumb');
    }

    private function runGeneratedModelTest($modelName, $modelFile)
    {
        $expected = file_get_contents(__DIR__ . '/model_data/' . $modelFile);
        $parsed = ParsedResult::getSampleParsedObject();
        $models = $parsed['modelList']->all();
        $model = $models[$modelName];

        $parsed = ParsedResult::getSampleParsedObject();
        $migrations = $parsed['migrationList']->all();
        

        if (is_null($this->modelGenerator))
        {
            $this->modelGenerator = new ModelGenerator();
            $this->migrationGenerator = new MigrationGenerator();
        }

        $generated = $this->modelGenerator->generate($model,$migrations);
        $this->assertEquals( preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $expected), preg_replace(array('/\s{2,}/', '/[\t\n]/'),' ', $generated));
        
    }
}