<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Views\Loaders;

use Spiral\Core\Component;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\LoaderInterface;
use Spiral\Views\ViewSource;

/**
 * Provides ability to wrap another loader in a given enviroment and process all view sources
 * before giving them to engine.
 *
 * Modifiers executed on template source BEFORE providing it to engine.
 */
class ModifiableLoader extends Component implements LoaderInterface
{
    use BenchmarkTrait;

    /**
     * Loaded to be used for file resolution.
     *
     * @var LoaderInterface
     */
    private $parent = null;

    /**
     * Required in order to give processors some context.
     *
     * @var EnvironmentInterface
     */
    private $environment = null;

    /**
     * @var \Spiral\Views\ProcessorInterface[]
     */
    protected $modifiers = [];

    /**
     * ProcessableLoader constructor.
     *
     * @param EnvironmentInterface $environment
     * @param LoaderInterface      $loader
     * @param array                $modifiers
     */
    public function __construct(
        EnvironmentInterface $environment,
        LoaderInterface $loader,
        array $modifiers = []
    ) {
        $this->environment = $environment;
        $this->parent = $loader;
        $this->modifiers = $modifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource(string $path): ViewSource
    {
        $source = $this->parent->getSource($path);

        foreach ($this->modifiers as $modifier) {
            $benchmark = $this->benchmark('process', $path . '@' . get_class($modifier));
            try {
                $source = $source->withCode(
                    $modifier->modify($this->environment, $source, $source->getCode())
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
    public function exists(string $path): bool
    {
        return $this->parent->exists($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespaces(): array
    {
        return $this->parent->getNamespaces();
    }

    /**
     * {@inheritdoc}
     */
    public function withExtension(string $extension = null): LoaderInterface
    {
        $wrapper = clone $this;
        $wrapper->parent = $wrapper->parent->withExtension($extension);

        return $wrapper;
    }
}