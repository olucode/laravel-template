<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use InvalidArgumentException;

trait RepositoryReplacement {

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

}
