<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler;

use Spiral\Stempler\Loader\LoaderInterface as StemplerLoaderInterface;
use Spiral\Stempler\Loader\Source;
use Spiral\Views\ContextInterface;
use Spiral\Views\Exception\EngineException;
use Spiral\Views\LoaderInterface;
use Spiral\Views\Traits\ProcessorTrait;

/**
 * Template source code loading and pre-processing (translation, env injection).
 */
final class StemplerLoader implements StemplerLoaderInterface
{
    use ProcessorTrait;

    /** @var LoaderInterface */
    private $loader;

    /** @var ContextInterface */
    private $context;

    /**
     * @param LoaderInterface $loader
     * @param array           $processors
     */
    public function __construct(LoaderInterface $loader, array $processors)
    {
        $this->loader = $loader;
        $this->processors = $processors;
    }

    /**
     * Lock loader to specific context.
     *
     * @param ContextInterface $context
     */
    public function setContext(ContextInterface $context): void
    {
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function load(string $path): Source
    {
        if ($this->context === null) {
            throw new EngineException('Unable to use StemplerLoader without given context.');
        }

        $source = $this->process(
            $this->loader->load($path),
            $this->context
        );

        return new Source($source->getCode(), $source->getFilename());
    }
}
