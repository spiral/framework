<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Views\Engines\Twig;

use Spiral\Views\Engines\Traits\ProcessorsTrait;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\LoaderInterface;

/**
 * Bridge between twig loader and spiral loader (i wish twig use interface for context).
 */
class LoaderBridge implements \Twig_LoaderInterface
{
    use ProcessorsTrait;

    /**
     * @var \Spiral\Views\LoaderInterface
     */
    private $loader;

    /**
     * @var \Spiral\Views\EnvironmentInterface
     */
    private $environment;

    /**
     * LoaderBridge constructor.
     *
     * @param \Spiral\Views\EnvironmentInterface $environment
     * @param \Spiral\Views\LoaderInterface      $loader
     * @param array                              $processors
     */
    public function __construct(
        EnvironmentInterface $environment,
        LoaderInterface $loader,
        array $processors
    ) {
        $this->environment = $environment;
        $this->loader = $loader;
        $this->processors = $processors;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceContext($name)
    {
        $source = $this->processSource($this->environment, $this->loader->getSource($name));

        return new \Twig_Source(
            $source->getCode(),
            $source->getName(),
            $source->getFilename()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        return $this->loader->getSource($name)->getFilename();
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        return $this->loader->getSource($name)->isFresh($time);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return $this->loader->exists($name);
    }
}