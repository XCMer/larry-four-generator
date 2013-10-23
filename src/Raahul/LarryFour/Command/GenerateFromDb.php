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
        // TODO: Use DB::getDriverName() instead of hardcoded database driver name
        // PostgreSQL isn't support by SchemaExtractor package. So we force it to use 'mysql' driver.
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
            if( DB::getDriverName() === 'mysql' ) {
                $tablesData[ $table ] = DB::select("DESCRIBE `$table`");
            } else if( DB::getDriverName() === 'pgsql' ) {
                // PostgreSQL port of MySQL DESCRIBE statement
                $tablesData[ $table ] = DB::select(
                    "SELECT a.oid, c.column_name AS \"Field\", COALESCE(c.column_default, 'NULL') AS \"Default\", c.is_nullable AS \"Null\",
                        CASE
                          WHEN c.udt_name = 'bool' THEN 'boolean'
                          WHEN c.udt_name = 'int2' THEN 'smallint' || COALESCE(NULLIF('('|| COALESCE(c.character_maximum_length::varchar, '') || ')', '()'), '')
                          WHEN c.udt_name = 'int4' THEN 'int'
                          WHEN c.udt_name = 'int8' THEN 'bigint'
                          WHEN c.udt_name LIKE 'float_' THEN 'float'
                          WHEN c.udt_name = 'timetz' THEN 'time'
                          WHEN c.udt_name = 'timestamptz' THEN 'timestamp'
                          WHEN c.udt_name ~ '_?bytea' THEN 'blob' || COALESCE(NULLIF('('|| COALESCE(c.character_maximum_length::varchar, '') || ')', '()'), '')
                          WHEN c.udt_name = 'numeric' THEN 'decimal' || COALESCE(NULLIF('('|| COALESCE(c.numeric_precision::varchar, '') || ',' || COALESCE(c.numeric_scale::varchar, '') || ')', '(,)'), '')
                          WHEN p.consrc LIKE '%ARRAY[%]%' THEN 'enum' || '(' || regexp_replace(regexp_replace(p.consrc, '.+ARRAY\[([^\[\]]+)\].+', '\\1'), '::[a-z0-9 ]+', '', 'g') || ')'
                        ELSE c.udt_name || COALESCE(NULLIF('(' || COALESCE(c.character_maximum_length::varchar, '') ||')', '()'), '')
                        END AS \"Type\",
                        CASE
                          WHEN c.column_default LIKE 'nextval(%)' THEN 'auto_increment'
                        END AS \"Extra\",
                        CASE
                          WHEN p.contype = 'p' THEN 'PRI'
                          WHEN p.contype = 'u' AND p.conkey::text LIKE '{_,%}' THEN 'MUL'
                          WHEN p.contype = 'u' THEN 'UNI'
                        END AS \"Key\"
                        FROM INFORMATION_SCHEMA.COLUMNS c
                        INNER JOIN (SELECT attrelid AS oid, attname FROM pg_attribute
                          WHERE attrelid = ( SELECT oid FROM pg_class WHERE relname = '$table') ) a ON a.attname = c.column_name
                          LEFT JOIN pg_constraint p ON p.conrelid = a.oid AND c.dtd_identifier::integer = ANY (p.conkey )
                        WHERE c.table_name = '$table'"
                );
            }
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


    /**
     * Verify if the database that we're using is supported by Larry
     */
    protected function verifyDriver()
    {
        $driver = DB::getDriverName();
        $supported = array('mysql', 'pgsql');
        if ( !in_array($driver, $supported)  )
        {
            $this->error("You are using an unsupported database driver. Only the following drivers are supported for now: " . implode(", ", $supported));
        }
    }


    /**
     * Get an array of tables that will have to be processed in the end
     * @return array List of table names
     */
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


    /**
     * Get the name of the selected database
     * @return string Database name
     */
    protected function getDbName()
    {
        if( DB::getDriverName() === 'mysql' ) {
            $select = DB::select('SELECT database() as dbname');
        } else if( DB::getDriverName() === 'pgsql' ) {
            $select = DB::select('SELECT current_database() as dbname');
        }

        return $select[0]->dbname;
    }


    /**
     * Get all the tables present in the database
     * @param  string $dbname The name of the database
     * @return array          An array of table names present
     */
    protected function getAllTables($dbname)
    {
        $tables = array();
        if( DB::getDriverName() === 'mysql' ) {
            $tableRows = DB::select('SHOW tables');
            $column = "Tables_in_{$dbname}";

            foreach ($tableRows as $row)
            {
                $tables[] = $row->$column;
            }
        } else if( DB::getDriverName() === 'pgsql' ) {
            $tableRows = DB::select(
                "SELECT table_name FROM information_schema.tables
                  WHERE table_type = 'BASE TABLE'
                  AND table_schema = 'public'
                  ORDER BY table_type, table_name"
            );
            foreach ($tableRows as $row)
            {
                $tables[] = $row->table_name;
            }
        }
        return $tables;
    }


    /**
     * Get the tables marked by the --only parameters, if any
     * @return array An array of table names marked by --only parameter
     */
    protected function getOnlyTables()
    {
        $onlyTables = explode(",", $this->option('only'));
        return array_filter( array_map('trim', $onlyTables) );
    }


    /**
     * Get the tables marked by the --except parameters, if any
     * @return array An array of table names marked by --except parameter
     */
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
        return array();
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
