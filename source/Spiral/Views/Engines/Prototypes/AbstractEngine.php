<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Views\Engines\Prototypes;

use Spiral\Core\Component;
use Spiral\Views\EngineInterface;
use Spiral\Views\EnvironmentInterface;
use Spiral\Views\LoaderInterface;

/**
 * ViewEngine with ability to switch environment and loader.
 */
abstract class AbstractEngine extends Component implements EngineInterface
{
    /**
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @param EnvironmentInterface $environment
     * @param LoaderInterface      $loader
     */
    public function __construct(
        EnvironmentInterface $environment,
        LoaderInterface $loader
    ) {
        $this->environment = $environment;
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function withLoader(LoaderInterface $loader): EngineInterface
    {
        $engine = clone $this;
        $engine->loader = $loader;

        return $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function withEnvironment(EnvironmentInterface $environment): EngineInterface
    {
        $engine = clone $this;
        $engine->environment = $environment;

        return $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $path, array $context = []): string
    {
        return $this->get($path)->render($context);
    }
}