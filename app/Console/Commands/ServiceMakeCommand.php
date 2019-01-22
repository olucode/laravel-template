<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ServiceMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Service';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    public function getStub()
    {
        $stub = '/stubs/service.stub';

        return __DIR__.$stub;
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $replace = [];

        $replace = $this->buildRepositoryReplacements($replace);

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Build the repository replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildRepositoryReplacements(array $replace)
    {
        $repositoryClass = $this->parseRepository($this->option('repo'));

        $baseClass = class_basename($repositoryClass);
        $modelName = str_ireplace('repository', '', $baseClass);

        if (! class_exists($repositoryClass)) {
            if ($this->confirm("{$repositoryClass} class does not exist. Do you want to generate it?", true)) {
                $this->call('make:repository', [
                    'name' => $repositoryClass,
                    'model' => ucfirst($modelName)
                ]);
            }
        }

        return array_merge($replace, [
            'DummyFullRepositoryClass' => $repositoryClass,
            'DummyRepositoryClass' => $baseClass,
            'DummyRepositoryVariable' => lcfirst($modelName),
        ]);
    }

    /**
     * Get the fully-qualified repository class name.
     *
     * @param  string  $repository
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseRepository($repository)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $repository)) {
            throw new InvalidArgumentException('Repository name contains invalid characters.');
        }

        $repository = trim(str_replace('/', '\\', $repository), '\\');

        if (! Str::startsWith($repository, $rootNamespace = $this->laravel->getNamespace())) {
            $repository = $rootNamespace.'Repositories\\'.$repository;
        }

        return $repository;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Services';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['repo', 'r', InputOption::VALUE_OPTIONAL, 'Generate a service class for the given repository.'],
        ];
    }
}
