<?php namespace Raahul\LarryFour\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        echo "Hello World";
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