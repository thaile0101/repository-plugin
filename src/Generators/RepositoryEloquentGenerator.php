<?php
namespace ThaiLe\Repository\Generators;

/**
 * Class RepositoryInterfaceGenerator
 * @package ThaiLe\Repository\Generators
 */
class RepositoryEloquentGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'Repository/eloquent';

    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getPath()
    {
        return sprintf('%s/%s/%sRepository.php',
            $this->getBasePath(),
            parent::getConfigGeneratorClassPath($this->getPathConfigNode(), true),
            $this->getName()
        );
    }

    /**
     * Get generator path config node.
     *
     * @return string
     */
    public function getPathConfigNode()
    {
        return 'repositories';
    }

    /**
     * Get array replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        $interface = parent::getRootNamespace() . parent::getConfigGeneratorClassPath('interfaces') . '\\' . $this->options['name'] . 'RepositoryInterface;';
        $interface = str_replace([
            "\\",
            '/'
        ], '\\', $interface);

        return array_merge(parent::getReplacements(), [
            'interface'    => $interface,
            'model' => $this->model,
        ]);
    }

    /**
     * Get root namespace.
     *
     * @return string
     */
    public function getRootNamespace()
    {
        return parent::getRootNamespace() . parent::getConfigGeneratorClassPath($this->getPathConfigNode());
    }
}