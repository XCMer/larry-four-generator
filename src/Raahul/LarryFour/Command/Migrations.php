<?php namespace Raahul\LarryFour\Command;

use \Raahul\LarryFour\Command\BaseCommand;

class Migrations extends BaseCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'larry:migrations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate only migration files from a Larry compatible input';

    /**
     * Execute this command, generating only migrations
     *
     * @param array $parsed An array of ModelList and MigrationList
     * @return void
     */
    protected function runCommand($parsed)
    {
        // Generate migrations
        $this->generateMigrations($parsed['migrationList']->all());
    }

}