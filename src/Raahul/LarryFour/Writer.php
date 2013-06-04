<?php namespace Raahul\LarryFour;


class Writer
{
    /**
     * The absolute path to the model folder including a trailing slash
     * @var string
     */
    private $modelPath;

    /**
     * The absolute path to the migration folder including a trailing slash
     * @var string
     */
    private $migrationPath;


    /**
     * Initialize the model and migration path
     * @param string $modelPath     The path to the model folder with trailing slash
     * @param string $migrationPath The path to the migration folder with trailing slash
     */
    public function __construct($modelPath, $migrationPath)
    {
        $this->modelPath = $modelPath;
        $this->migrationPath = $migrationPath;
    }


    /**
     * Write a laravel migration file
     * @param  string $migrationContent  Content of the migration file
     * @param  string $migrationFilename Filename of the migration file
     */
    public function writeMigration($migrationContent, $migrationFilename)
    {
        file_put_contents($this->migrationPath . $migrationFilename, $migrationContent);
    }


    /**
     * Write a laravel model file
     * @param  string $modelContent  Content of the model file
     * @param  string $modelFilename Filename of the model file
     */
    public function writeModel($modelContent, $modelFilename)
    {
        file_put_contents($this->modelPath . $modelFilename, $modelContent);
    }
}