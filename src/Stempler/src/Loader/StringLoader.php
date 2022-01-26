<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Loader;

use Spiral\Stempler\Exception\LoaderException;

final class StringLoader implements LoaderInterface
{
    /** @var array */
    private $paths = [];

    public function set(string $path, string $content): void
    {
        $this->paths[$path] = $content;
    }

    /**
     * @inheritDoc
     */
    public function load(string $path): Source
    {
        if (!array_key_exists($path, $this->paths)) {
            throw new LoaderException("Unable to load path `{$path}`");
        }

        return new Source($this->paths[$path]);
    }
}
