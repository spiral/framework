<?php

declare(strict_types=1);

namespace Spiral\Stempler\Compiler;

use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Parser\Context;

/**
 * Result contains generated template content and line numbers associated with root template and
 * with the source line for imported templates.
 *
 * The map is generated using set of Contexts associated with each imported block.
 */
final class Result
{
    private string $content = '';

    /** @var Location[] */
    private array $locations = [];

    private ?Location $parent = null;

    public function withinContext(?Context $ctx, callable $body): void
    {
        if ($ctx === null || $ctx->getPath() === null) {
            $body($this);
            return;
        }

        try {
            $this->parent = Location::fromContext($ctx, $this->parent);
            $body($this);
        } finally {
            $this->parent = $this->parent->parent;
        }
    }

    public function push(string $content, Context $ctx = null): void
    {
        if ($ctx !== null && $ctx->getPath() !== null) {
            $this->locations[\strlen($this->content)] = Location::fromContext($ctx, $this->parent);
        }

        $this->content .= $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Get all template paths involved in final template.
     */
    public function getPaths(): array
    {
        $paths = [];

        // We can scan top level only
        foreach ($this->locations as $loc) {
            if (!\in_array($loc->path, $paths, true)) {
                $paths[] = $loc->path;
            }
        }

        return $paths;
    }

    /**
     * Generates sourcemap for exception handling and cache invalidation.
     */
    public function getSourceMap(LoaderInterface $loader): SourceMap
    {
        $locations = [];

        foreach ($this->locations as $offset => $location) {
            $locations[$offset] = $location;
        }

        return SourceMap::calculate($this->content, $locations, $loader);
    }
}
