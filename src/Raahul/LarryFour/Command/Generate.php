<?php namespace Raahul\LarryFour\Command;

use \Raahul\LarryFour\Command\BaseCommand;

class Generate extends BaseCommand {

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
     * Execute this command, generating both models and migrations
     *
     * @param array $parsed An array of ModelList and MigrationList
     * @return void
     */
    protected function runCommand($parsed)
    {
        // Generate migrations
        $this->generateMigrations($parsed['migrationList']->all());

        // Generate models
        $this->generateModels($parsed['modelList']->all(), $parsed['migrationList']->all());
    }

}