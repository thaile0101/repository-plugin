<?php

namespace ThaiLe\Repository\Generators;

/**
 * Class RepositoryInterfaceGenerator
 * @package ThaiLe\Repository\Generators
 */
class RepositoryInterfaceGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'Repository/interface';

    /**
     * Get generator path config node.
     *
     * @return string
     */
    public function getPathConfigNode()
    {
        return 'interfaces';
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

    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getPath()
    {
        return sprintf('%s/%s/%sRepositoryInterface.php',
            $this->getBasePath(),
            parent::getConfigGeneratorClassPath($this->getPathConfigNode(), true),
            $this->getName()
        );
    }
}