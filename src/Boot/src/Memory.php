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
     * @param non-empty-string|null $filename Cache filename.
     */
    public function loadData(string $section, ?string &$filename = null): mixed
    {
        $filename = $this->getFilename($section);

        if (!\file_exists($filename)) {
            return null;
        }

        $fp = false;
        $lock = false;

        try {
            $fp = \fopen($filename, 'r');
            if ($fp === false) {
                return null;
            }

            $lock = \flock($fp, \LOCK_SH | \LOCK_NB);

            if ($lock === false) {
                return null;
            }

            return include($filename);
        } catch (\Throwable) {
            return null;
        } finally {
            $lock === false or \flock($fp, \LOCK_UN);
            $fp === false or \fclose($fp);
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
