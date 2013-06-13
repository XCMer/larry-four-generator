<?php namespace Raahul\LarryFour\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use \Raahul\LarryFour\Writer;
use \Raahul\LarryFour\MigrationList;
use \Raahul\LarryFour\Generator\MigrationGenerator;
use \Raahul\LarryFour\DbParser;
use \Raahul\SchemaExtractor\SchemaExtractor;
use \Illuminate\Support\Facades\DB;

class GenerateFromDb extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'larry:fromdb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate migration files from the active database';

    /**
     * Instance of the Larry Writer class
     *
     * @var \Raahul\LarryFour\Writer
     */
    protected $larryWriter;

    /**
     * Instance of the migration generator
     * @var \Raahul\LarryFour\Generator\MigrationGenerator
     */
    protected $migrationGenerator;

    /**
     * Instance of the database parser
     * @var \Raahul\LarryFour\DbParser
     */
    protected $dbParser;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Initialize the migration generator
        $this->migrationGenerator = new MigrationGenerator();

        // Initial the paths to the model and migration folder, and then the
        // writer class
        $modelPath = app_path() . '/models/';
        $migrationPath = app_path() . '/database/migrations/';

        $this->larryWriter = new Writer($modelPath, $migrationPath);

        // Initialize the database parser
        $this->dbParser = new DbParser(new MigrationList(), new SchemaExtractor(), 'mysql');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        // Verify whether we're running on a supported driver
        $this->verifyDriver();

        // Get all tables to be processed
        $tables = $this->getAllTablesToBeProcessed();

        // Print it for confirmation
        $this->info("Migrations for the following tables will be created:");
        $this->info(implode("\n", $tables));
        $confirm = $this->confirm('Do you wish to continue? [yes|no]', false);

        if (!$confirm) die();

        // Now, let's start processing
        $tablesData = array();
        foreach ($tables as $table)
        {
            $tablesData[ $table ] = DB::select("DESCRIBE $table");
        }

        // Get migrations
        $migrationList = $this->dbParser->parse($tablesData);

        // Generate migrations
        $this->generateMigrations($migrationList->all());
    }

    /**
     * This command has to be overriden in individual commands to either generate
     * models or migrations or both
     *
     * @param  array $parsed An array of ModelList and MigrationList
     * @return void
     */
    protected function runCommand($parsed)
    {
        throw new \Exception("The runCommand function has to be overriden.");
    }

    protected function verifyDriver()
    {
        $driver = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $supported = array('mysql');
        if ( !in_array($driver, $supported)  )
        {
            $this->error("You are using an unsupported database driver. Only the following drivers are supported for now: " . implode(", ", $supported));
        }
    }

    protected function getAllTablesToBeProcessed()
    {
        // Get all the tables from the database
        $allTables = $this->getAllTables($this->getDbName());

        // Get --only tables if any
        $onlyTables = $this->getOnlyTables();

        // Get --except tables if any
        $exceptTables = $this->getExceptTables();

        // If the --only parameter is present, do some error checking on whether
        // those tables exist
        if ($onlyTables)
        {
            foreach ($onlyTables as $t)
            {
                if (!in_array($t, $allTables))
                {
                    $this->error("\nTable {$t} is not present in your database.\n");
                    die();
                }
            }

            return $onlyTables;
        }

        // If the --except paramter is present, remove those tables from the allTables
        // array and return
        // We also remove the laravel `migrations` table
        else if ($exceptTables)
        {
            return array_filter($allTables, function($element) use($exceptTables) {
                if ( (in_array($element, $exceptTables))
                    or ($element == 'migrations') )
                {
                    return false;
                }

                return true;
            });
        }

        // In the normal case, we only remove laravel's migration table
        else
        {
            return array_filter($allTables, function($element) {
                if ( $element == 'migrations' )
                {
                    return false;
                }

                return true;
            });
        }
    }

    protected function getDbName()
    {
        $select = DB::select('SELECT database() as dbname');

        return $select[0]->dbname;
    }

    protected function getAllTables($dbname)
    {
        $tables = array();
        $tableRows = DB::select('SHOW tables');
        $column = "Tables_in_{$dbname}";

        foreach ($tableRows as $row)
        {
            $tables[] = $row->$column;
        }

        return $tables;
    }

    protected function getOnlyTables()
    {
        $onlyTables = explode(",", $this->option('only'));
        return array_filter( array_map('trim', $onlyTables) );
    }

    protected function getExceptTables()
    {
        $exceptTables = explode(",", $this->option('except'));
        return array_filter( array_map('trim', $exceptTables) );
    }

    /**
     * Generates all the migrations, given a list of migrations
     * @param  array $migrations An array of migration objects
     */
    protected function generateMigrations($migrations)
    {
        foreach ($migrations as $migration)
        {
            $filename = date('Y_m_d_His') . '_create_' . $migration->tableName . '_table.php';
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
            array('filename', InputArgument::OPTIONAL, 'Name of the input file.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('only', '', InputOption::VALUE_REQUIRED, 'Tables that should be processed, while rest excluded'),
            array('except', '', InputOption::VALUE_REQUIRED, 'Tables that should excluded'),
        );
    }

}