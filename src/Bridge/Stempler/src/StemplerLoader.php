<?php

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

    private ?ContextInterface $context = null;

    public function __construct(
        private readonly LoaderInterface $loader,
        array $processors
    ) {
        $this->processors = $processors;
    }

    /**
     * Lock loader to specific context.
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
