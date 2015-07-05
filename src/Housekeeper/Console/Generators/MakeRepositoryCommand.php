<?php

namespace Housekeeper\Console\Generators;

use Housekeeper\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MakeRepositoryCommand
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Console\Generators
 */
class MakeRepositoryCommand extends GeneratorCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'housekeeper:make:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a repository file.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Housekeeper:Repository';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        parent::fire();

    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('adjustable') && $this->option('cacheable')) {
            return __DIR__ . '/stubs/repository-adjustable-cacheable.stub';
        } elseif ($this->option('adjustable')) {
            return __DIR__ . '/stubs/repository-adjustable.stub';
        } elseif ($this->option('cacheable')) {
            return __DIR__ . '/stubs/repository-cacheable.stub';
        } else {
            return __DIR__ . '/stubs/repository.stub';
        }
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Repositories';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['adjustable', null, InputOption::VALUE_NONE, 'Make this repository adjustable by pass it criteria.'],
            ['cacheable', null, InputOption::VALUE_NONE, 'Allow cache method result automatically.'],
        ];
    }


}