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

/**
 * Loads view content from the directory.
 */
final class DirectoryLoader implements LoaderInterface
{
    /** @var string */
    private $directory;

    /** @var string */
    private $extension;

    public function __construct(string $directory, string $extension = '.dark.php')
    {
        $this->directory = $directory;
        $this->extension = $extension;
    }

    /**
     *
     * @throws LoaderException
     */
    public function load(string $path): Source
    {
        $filename = sprintf(
            '%s%s%s%s',
            $this->directory,
            DIRECTORY_SEPARATOR,
            $path,
            $this->extension
        );

        if (!file_exists($filename)) {
            throw new LoaderException("Unable to load `{$path}`, no such file");
        }

        return new Source(file_get_contents($filename), $filename);
    }
}
