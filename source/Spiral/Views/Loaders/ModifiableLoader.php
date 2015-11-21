<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Loaders;

use Spiral\Core\Component;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Views\LoaderInterface;
use Spiral\Views\ModifierInterface;

/**
 * Wraps at top of views loader and applies source modifiers.
 */
class ModifiableLoader extends Component implements LoaderInterface
{
    /**
     * Benchmarking modifiers.
     */
    use BenchmarkTrait;

    /**
     * @var LoaderInterface
     */
    protected $loader = null;

    /**
     * @var ModifierInterface[]
     */
    protected $modifiers = [];

    /**
     * @param LoaderInterface     $loader
     * @param ModifierInterface[] $modifiers
     */
    public function __construct(LoaderInterface $loader, array $modifiers = [])
    {
        $this->loader = $loader;
        $this->modifiers = $modifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource($path)
    {
        $source = $this->loader->getSource($path);

        foreach ($this->modifiers as $modifier) {
            $benchmark = $this->benchmark('modify', $path . '@' . get_class($modifier));
            try {
                $source = $modifier->modify(
                    $source,
                    $this->viewNamespace($path),
                    $this->viewName($path)
                );
            } finally {
                $this->benchmark($benchmark);
            }
        }

        return $source;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        return $this->loader->getCacheKey($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        return $this->loader->isFresh($name, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespaces()
    {
        return $this->loader->getNamespaces();
    }

    /**
     * {@inheritdoc}
     */
    public function viewNamespace($path)
    {
        return $this->loader->viewNamespace($path);
    }

    /**
     * {@inheritdoc}
     */
    public function viewName($path)
    {
        return $this->loader->viewName($path);
    }

    /**
     * {@inheritdoc}
     */
    public function withExtension($extension)
    {
        $wrapper = clone $this;
        $wrapper->loader = $wrapper->loader->withExtension($extension);

        return $wrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function localFilename($path)
    {
        return $this->loader->localFilename($path);
    }
}