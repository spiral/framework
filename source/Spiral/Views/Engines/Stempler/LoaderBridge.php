<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Views\Engines\Stempler;

use Spiral\Stempler\LoaderInterface;
use Spiral\Stempler\StemplerSource;

class LoaderBridge implements LoaderInterface
{
    /**
     * @var \Spiral\Views\LoaderInterface
     */
    private $loader;

    /**
     * @param \Spiral\Views\LoaderInterface $loader
     */
    public function __construct(\Spiral\Views\LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @param string $path
     *
     * @return StemplerSource
     */
    public function getSourceContext(string $path): StemplerSource
    {
        $context = $this->loader->getSourceContext($path);

        return new StemplerSource($context->getFilename(), $context->getCode());
    }
}