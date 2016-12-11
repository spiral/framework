<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Views\Engines\Twig;

use Spiral\Views\ProcessableLoader;

/**
 * Twig loader wrapper. It deprecated to use twig without this wrapper, but wrapper is deprecated
 * as well. :)
 */
class ContextLoader extends ProcessableLoader implements \Twig_SourceContextLoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSourceContext($name)
    {
        return new \Twig_Source($this->getSource($name), $name, $this->localFilename($name));
    }
}