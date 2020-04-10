<?php

namespace Wptomo\Hane\Console;

use Illuminate\Console\GeneratorCommand;

class ConverterMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:converter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new converter class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Converter';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/converter.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Converters';
    }

    protected function buildClass($name)
    {
        $converterNamespace = $this->getNamespace($name);

        $replace = [];

        $replace["use {$converterNamespace}\Converters;\n"] = '';

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }
}
