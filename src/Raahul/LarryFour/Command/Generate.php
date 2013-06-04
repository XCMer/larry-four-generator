<?php namespace Raahul\LarryFour\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use \Raahul\LarryFour\Writer;
use \Raahul\LarryFour\Parser;
use \Raahul\LarryFour\Parser\FieldParser;
use \Raahul\LarryFour\Parser\ModelDefinitionParser;
use Raahul\LarryFour\Exception\ParseError;
use \Raahul\LarryFour\ModelList;
use \Raahul\LarryFour\MigrationList;
use \Raahul\LarryFour\Generator\ModelGenerator;
use \Raahul\LarryFour\Generator\MigrationGenerator;

class Generate extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'larry:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate model and migration files from an intuitive text input';

    /**
     * Instance of the Larry Writer class
     *
     * @var \Raahul\LarryFour\Writer
     */
    private $larryWriter;

    /**
     * Instance of the Larry Parser
     *
     * @var \Raahul\LarryFour\Parser
     */
    private $parser;

    /**
     * Instance of the model generator
     * @var \Raahul\LarryFour\Generator\ModelGenerator
     */
    private $modelGenerator;

    /**
     * Instance of the migration generator
     * @var \Raahul\LarryFour\Generator\MigrationGenerator
     */
    private $migrationGenerator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Initialize the parser
        $this->parser = new Parser(new FieldParser(),
            new ModelDefinitionParser(),
            new ModelList(),
            new MigrationList()
        );

        // Initialize the generators
        $this->modelGenerator = new ModelGenerator();
        $this->migrationGenerator = new MigrationGenerator();

        // Initial the paths to the model and migration folder, and then the
        // writer class
        $modelPath = app_path() . '/models/';
        $migrationPath = app_path() . '/database/migrations/';

        $this->larryWriter = new Writer($modelPath, $migrationPath);
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // Check if the input file exists
        $filename = base_path() . '/' . $this->argument('filename');
        if (!file_exists($filename))
        {
            $this->error("The input filename you specified doesn't exist: {$filename}");
        }

        // If the file exists, get it contents and parse it
        try {
            $parsed = $this->parser->parse(file_get_contents($filename));
        } catch (ParseError $e) {
            $this->error($e->getMessage());
            die();
        }

        // If parsing is done right, let's proceed with the generation
        $models = $parsed['modelList']->all();
        $migrations = $parsed['migrationList']->all();

        // Generate models
        foreach ($models as $model)
        {
            $this->larryWriter->writeModel(
                $this->modelGenerator->generate($model),
                $model->modelName . '.php'
            );
            $this->info("Wrote model: " . $model->modelName . '.php');
        }

        // Generate migrations
        foreach ($migrations as $migration)
        {
            $filename = date('Y_m_d_His') . '_create_' . $migration->tableName . '_table';
            $this->larryWriter->writeMigration(
                $this->migrationGenerator->generate($migration),
                $filename
            );
            $this->info("Wrote migration: {$filename}");
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('filename', InputArgument::REQUIRED, 'Name of the input file.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }

}