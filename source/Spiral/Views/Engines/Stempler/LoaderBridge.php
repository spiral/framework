<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Engines\Stempler;

use Spiral\Stempler\LoaderInterface;
use Spiral\Stempler\StemplerSource;
use Spiral\Views\Engines\Traits\ProcessorsTrait;
use Spiral\Views\EnvironmentInterface;

class LoaderBridge implements LoaderInterface
{
    use ProcessorsTrait;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * @param EnvironmentInterface          $environment
     * @param \Spiral\Views\LoaderInterface $loader
     * @param array                         $processors
     */
    public function __construct(
        EnvironmentInterface $environment,
        \Spiral\Views\LoaderInterface $loader,
        array $processors
    ) {
        $this->environment = $environment;
        $this->loader = $loader;
        $this->processors = $processors;
    }

    /**
     * @param string $path
     *
     * @return StemplerSource
     */
    public function getSource(string $path): StemplerSource
    {
        $source = $this->loader->getSource($path);

        return new StemplerSource(
            $source->getFilename(),
            $this->processSource($this->environment, $source)->getCode()
        );
    }
}