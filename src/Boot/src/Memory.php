<?php

declare(strict_types=1);

namespace Spiral\Boot;

use Spiral\Files\FilesInterface;

/**
 * File based memory storage.
 */
final class Memory implements MemoryInterface
{
    // data file extension
    private const EXTENSION = 'php';

    private readonly string $directory;

    public function __construct(
        string $directory,
        private readonly FilesInterface $files
    ) {
        $this->directory = \rtrim($directory, '/');
    }

    /**
     * @param string $filename Cache filename.
     */
    public function loadData(string $section, string &$filename = null): mixed
    {
        $filename = $this->getFilename($section);

        if (!\file_exists($filename)) {
            return null;
        }

        try {
            return include($filename);
        } catch (\Throwable) {
            return null;
        }
    }

    public function saveData(string $section, mixed $data): void
    {
        $this->files->write(
            $this->getFilename($section),
            '<?php return ' . \var_export($data, true) . ';',
            FilesInterface::RUNTIME,
            true
        );
    }

    /**
     * Get extension to use for runtime data or configuration cache.
     *
     * @param string $name Runtime data file name (without extension).
     */
    private function getFilename(string $name): string
    {
        //Runtime cache
        return \sprintf(
            '%s/%s.%s',
            $this->directory,
            \strtolower(\str_replace(['/', '\\'], '-', $name)),
            self::EXTENSION
        );
    }
}
