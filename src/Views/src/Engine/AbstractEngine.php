<?php

declare(strict_types=1);

namespace Spiral\Views\Engine;

use Spiral\Views\EngineInterface;
use Spiral\Views\Exception\EngineException;
use Spiral\Views\LoaderInterface;

/**
 * ViewEngine with ability to switch environment and loader.
 */
abstract class AbstractEngine implements EngineInterface
{
    protected const EXTENSION = '';

    protected ?string $extension = null;
    protected ?LoaderInterface $loader = null;

    public function withLoader(LoaderInterface $loader): EngineInterface
    {
        $engine = clone $this;
        $engine->loader = $loader->withExtension($this->extension ?? static::EXTENSION);

        return $engine;
    }

    public function getLoader(): LoaderInterface
    {
        if (empty($this->loader)) {
            throw new EngineException('No associated loader found');
        }

        return $this->loader;
    }
}
