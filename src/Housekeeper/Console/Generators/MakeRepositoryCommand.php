<?php

namespace Housekeeper\Console\Generators;

use Symfony\Component\Console\Input\InputOption;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;

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
    protected $signature = 'housekeeper:make' .
    ' {name : The name of the repository}' .
    ' {--cache= : Chose from 2 strategies to caching result: individual, unforgettable}' .
    ' {--ci : Short for "--cache=individual"}' .
    ' {--cu : Short for "--cache=unforgettable"}' .
    ' {--a|adjustment : Allow you to reuse queries}' .
    ' {--metadata : Convert all result that implemented `Arrayable` to array automatically}' .
    ' {--u|understanding : With frequently-used Eloquent-Style query methods}' .
    ' {--vintage : With backward compatible APIs for Housekeeper `0.9.x`}' .
    ' {--create= : Create a new model file for the repository.}' .
    ' {--model= : Specify the model used by the repository (Root Namespace "\App").}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a Housekeeper repository class file.';

    /**
     * @var string
     */
    protected $type = 'Repository';

    /**
     * @var array
     */
    protected $cacheAbilities = ['individual', 'unforgettable'];


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $name = $this->parseName($this->getNameInput());

        $path = $this->getPath($name);

        if ($this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . " \"{$name}\" already exists!");

            return;
        }

        if ( ! $this->checkOptions()) {
            return;
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->buildClass($name));

        $this->info($this->type . " \"{$name}\" created successfully.");

        // If also needs a new model file
        if ($model = $this->option('create')) {
            $this->call('make:model', ['name' => $model]);
        }
    }
    
    /**
     * @return bool
     */
    protected function checkOptions()
    {
        $cache = $this->option('cache');

        if ($cache && ! in_array($cache, $this->cacheAbilities)) {
            $this->error("The \"cache\" option must be one of these: \"" . implode(',', $this->cacheAbilities) . "\".");

            return false;
        }

        return true;
    }
    
    /**
     * Build the class with the given name.
     *
     * @param  string $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this
            ->replaceNamespace($stub, $name)
            ->replaceModel($stub)
            ->replaceAbility($stub)
            ->replaceClass($stub, $name);
    }

    /**
     * @param $stub
     * @return $this
     */
    protected function replaceModel(&$stub)
    {
        $model = '';

        if ($model = ($this->option('create') ?: $this->option('model'))) {
            $rootNamespace  = $this->getLaravel()->getNamespace();
            $modelNamespace = (Str::startsWith($model, $rootNamespace) ? '\\' : '\\App\\');
            $model          = "return {$modelNamespace}{$model}::class;";
        } else {
            $model = '//';
        }

        $stub = str_replace('DummyModel', $model, $stub);

        return $this;
    }
    
    /**
     * @param $stub
     * @return $this
     */
    protected function replaceAbility(&$stub)
    {
        $use   = '';
        $trait = '';

        $traits = [];

        if ($this->option('adjustment')) {
            $use .= "use Housekeeper\\Abilities\\Adjustment;\n";
            $traits[] = "Adjustment";
        }

        if ($cache = $this->option('cache')) {
            $cache = ucfirst($cache);

            $use .= "use Housekeeper\\Abilities\\Cache\\{$cache};\n";
            $traits[] = $cache;
        } elseif ($this->option('ci')) {
            $use .= "use Housekeeper\\Abilities\\Cache\\Individual;\n";
            $traits[] = 'Individual';
        } elseif ($this->option('cu')) {
            $use .= "use Housekeeper\\Abilities\\Cache\\Unforgettable;\n";
            $traits[] = 'Unforgettable';
        }

        if ($this->option('metadata')) {
            $use .= "use Housekeeper\\Abilities\\Metadata;\n";
            $traits[] = "Metadata";
        }

        if ($this->option('understanding')) {
            $use .= "use Housekeeper\\Abilities\\Understanding;\n";
            $traits[] = "Understanding";
        }

        if ($this->option('vintage')) {
            $use .= "use Housekeeper\\Abilities\\Vintage;\n";
            $traits[] = "Vintage";
        }

        if ( ! empty($traits)) {
            $trait = "\n    use " . implode(',', $traits) . ";\n";
        }

        $stub = str_replace('DummyUse', $use, $stub);
        $stub = str_replace('DummyTrait', $trait, $stub);

        return $this;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/repository.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        $config = $this->getLaravel()->make('config');

        $namespace = '\\' .
            trim(
                str_replace(
                    '/', '\\',
                    $config['housekeeper']['directory']
                ),
                '\\'
            );

        return $rootNamespace . $namespace;
    }

}