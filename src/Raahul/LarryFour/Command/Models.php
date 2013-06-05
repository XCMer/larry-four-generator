<?php namespace Raahul\LarryFour\Command;

use \Raahul\LarryFour\Command\BaseCommand;

class Models extends BaseCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'larry:models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate only model files from a Larry compatible input';

    /**
     * Execute this command, generating only models
     *
     * @param array $parsed An array of ModelList and MigrationList
     * @return void
     */
    protected function runCommand($parsed)
    {
        // Generate models
        $this->generateModels($parsed['modelList']->all());
    }

}