<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Views\Engines\Twig;

use Spiral\Views\LoaderInterface;

/**
 * Bridge between twig loader and spiral loader (i wish twig use interface for context).
 */
class LoaderBridge implements \Twig_LoaderInterface
{
    /**
     * @var \Spiral\Views\LoaderInterface
     */
    private $loader;

    /**
     * @param \Spiral\Views\LoaderInterface $loader
     */
    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceContext($name)
    {
        $context = $this->loader->getSource($name);

        return new \Twig_Source(
            $context->getCode(),
            $context->getName(),
            $context->getFilename()
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