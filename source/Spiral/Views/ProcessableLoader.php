<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Views;

use Spiral\Core\Component;
use Spiral\Debug\Traits\BenchmarkTrait;

/**
 * Provides ability to wrap another loader in a given enviroment and process all view sources
 * before giving them to engine.
 */
class ProcessableLoader extends Component implements LoaderInterface
{
    use BenchmarkTrait;

    /**
     * Loaded to be used for file resolution.
     *
     * @var LoaderInterface
     */
    private $loader = null;

    /**
     * Required in order to give processors some context.
     *
     * @var EnvironmentInterface
     */
    private $environment = null;

    /**
     * @var ProcessorInterface[]
     */
    protected $processors = [];

    /**
     * ProcessableLoader constructor.
     *
     * @param EnvironmentInterface $environment
     * @param LoaderInterface      $loader
     * @param array                $processors
     */
    public function __construct(
        EnvironmentInterface $environment,
        LoaderInterface $loader,
        array $processors = []
    ) {
        $this->environment = $environment;
        $this->loader = $loader;
        $this->processors = $processors;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource($path)
    {
        $source = $this->loader->getSource($path);

        foreach ($this->processors as $processor) {
            $benchmark = $this->benchmark('process', $path . '@' . get_class($processor));
            try {
                $source = $processor->modify(
                    $this->environment,
                    $source,
                    $this->fetchNamespace($path),
                    $this->fetchName($path)
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
    public function getCacheKey($name): string
    {
        return $this->loader->getCacheKey($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time): bool
    {
        return $this->loader->isFresh($name, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespaces(): array
    {
        return $this->loader->getNamespaces();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchNamespace(string $path): string
    {
        return $this->loader->fetchNamespace($path);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchName(string $path): string
    {
        return $this->loader->fetchName($path);
    }

    /**
     * {@inheritdoc}
     */
    public function withExtension(string $extension = null): LoaderInterface
    {
        $wrapper = clone $this;
        $wrapper->loader = $wrapper->loader->withExtension($extension);

        return $wrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function localFilename(string $path): string
    {
        return $this->loader->localFilename($path);
    }
}