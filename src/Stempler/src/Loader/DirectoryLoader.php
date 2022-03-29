<?php

declare(strict_types=1);

namespace Spiral\Stempler\Loader;

use Spiral\Stempler\Exception\LoaderException;

/**
 * Loads view content from the directory.
 */
final class DirectoryLoader implements LoaderInterface
{
    public function __construct(
        private readonly string $directory,
        private readonly string $extension = '.dark.php'
    ) {
    }

    /**
     * @throws LoaderException
     */
    public function load(string $path): Source
    {
        $filename = \sprintf(
            '%s%s%s%s',
            $this->directory,
            DIRECTORY_SEPARATOR,
            $path,
            $this->extension
        );

        if (!\file_exists($filename)) {
            throw new LoaderException(\sprintf('Unable to load `%s`, no such file', $path));
        }

        return new Source(\file_get_contents($filename), $filename);
    }
}
