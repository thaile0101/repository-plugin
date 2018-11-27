<?php

namespace Elidev\Repository\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

abstract class Generator
{
    /**
     * The stub name.
     *
     * @var string
     */
    protected $stub;

    /**
     * The file system
     * @var $filesystem
     */
    protected $filesystem;

    /**
     * The array of options.
     *
     * @var array
     */
    protected $options;


    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    abstract public function getPath();

    /**
     * Get parent config node.
     *
     * @return string
     */
    abstract public function getPathConfigNode();

    /**
     * RepositoryInterfaceGenerator
     * @param $options
     */
    public function __construct(array $options = [])
    {
        $this->filesystem = new Filesystem();
        $this->options = $options;
    }

    /**
     * Execute the generator
     * @return mixed
     */
    public function execute()
    {
        $desPath = $this->getPath();
        $source = $this->getStub();

        if (!$this->filesystem->isDirectory($dir = dirname($desPath))) {
            $this->filesystem->makeDirectory($dir, 0777, true, true);
        }

        $this->filesystem->put($desPath, $source);
    }

    /**
     * Get the template for generator
     */
    public function getStub()
    {
        $path = app_path();

        if(!file_exists($path . '/Stubs/' . $this->stub . '.stub')){
            $path = __DIR__;
        }

        return (new Stub($path . '/Stubs/' . $this->stub . '.stub', $this->getReplacements()))->render();
    }

    /**
     * Get template replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        return [
            'class'          => $this->getClass(),
            'namespace'      => $this->getNamespace(),
            'root_namespace' => $this->getRootNamespace()
        ];
    }

    /**
     * Get class namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        $segments = $this->getSegments();
        array_pop($segments);
        $rootNamespace = $this->getRootNamespace();
        if ($rootNamespace == false) {
            return null;
        }

        return 'namespace ' . rtrim($rootNamespace . '\\' . implode($segments, '\\'), '\\') . ';';
    }

    /**
     * Get paths of namespace.
     *
     * @return array
     */
    public function getSegments()
    {
        return explode('/', $this->getName());
    }

    /**
     * Get root namespace.
     *
     * @return string
     */
    public function getRootNamespace()
    {
        return config('repository.generator.rootNamespace', app()->getNamespace());
    }

    /**
     * Get class name.
     *
     * @return string
     */
    public function getClass()
    {
        return Str::studly(class_basename($this->getName()));
    }

    /**
     * Get name input.
     *
     * @return string
     */
    public function getName()
    {
        $name = $this->name;
        if (str_contains($this->name, '\\')) {
            $name = str_replace('\\', '/', $this->name);
        }

        if (str_contains($this->name, '/')) {
            $name = str_replace('/', '/', $this->name);
        }

        return Str::studly(str_replace(' ', '/', ucwords(str_replace('/', ' ', $name))));
    }

    /**
     * Get class-specific output paths.
     *
     * @param $class
     *
     * @return string
     */
    public function getConfigGeneratorClassPath($class, $directoryPath = false)
    {

        switch ($class) {
            case ('models' === $class):
                $path = config('repository.generator.paths.models', 'Models');
                break;
            case ('repositories' === $class):
                $path = config('repository.generator.paths.repositories', 'Repositories/Eloquents');
                break;
            case ('interfaces' === $class):
                $path = config('repository.generator.paths.interfaces', 'Repositories/Contracts');
                break;
            default:
                $path = '';
        }

        if ($directoryPath) {
            $path = str_replace('\\', '/', $path);
        } else {
            $path = str_replace('/', '\\', $path);
        }


        return $path;
    }

    /**
     * Get base path of destination file.
     *
     * @return string
     */
    public function getBasePath()
    {
        return config('repository.generator.basePath', app_path());
    }

    /**
     * Get options.
     *
     * @return string
     */
    public function getOptions()
    {
        return $this->options;
    }


    /**
     * Determinte whether the given key exist in options array.
     *
     * @param  string $key
     *
     * @return boolean
     */
    public function hasOption($key)
    {
        return array_key_exists($key, $this->options);
    }


    /**
     * Get value from options by given key.
     *
     * @param  string      $key
     * @param  string|null $default
     *
     * @return string
     */
    public function getOption($key, $default = null)
    {
        if (!$this->hasOption($key)) {
            return $default;
        }

        return $this->options[$key] ?: $default;
    }


    /**
     * Helper method for "getOption".
     *
     * @param  string      $key
     * @param  string|null $default
     *
     * @return string
     */
    public function option($key, $default = null)
    {
        return $this->getOption($key, $default);
    }

    /**
     * Handle call to __get method.
     *
     * @param  string $key
     *
     * @return string|mixed
     */
    public function __get($key)
    {
        if (property_exists($this, $key)) {
            return $this->{$key};
        }

        return $this->option($key);
    }
}