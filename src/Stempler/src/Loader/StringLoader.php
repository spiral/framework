<?php

declare(strict_types=1);

namespace Spiral\Stempler\Loader;

use Spiral\Stempler\Exception\LoaderException;

final class StringLoader implements LoaderInterface
{
    private array $paths = [];

    public function set(string $path, string $content): void
    {
        $this->paths[$path] = $content;
    }

    public function load(string $path): Source
    {
        if (!\array_key_exists($path, $this->paths)) {
            throw new LoaderException(\sprintf('Unable to load path `%s`', $path));
        }

        return new Source($this->paths[$path]);
    }
}
