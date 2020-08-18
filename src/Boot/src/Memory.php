<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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

    /** @var string */
    private $directory;

    /** @var FilesInterface */
    private $files = null;

    /**
     * @param string         $directory
     * @param FilesInterface $files
     */
    public function __construct(string $directory, FilesInterface $files)
    {
        $this->directory = rtrim($directory, '/');
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $filename Cache filename.
     */
    public function loadData(string $section, string &$filename = null)
    {
        $filename = $this->getFilename($section);

        if (!file_exists($filename)) {
            return null;
        }

        try {
            return include($filename);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveData(string $section, $data): void
    {
        $this->files->write(
            $this->getFilename($section),
            '<?php return ' . var_export($data, true) . ';',
            FilesInterface::RUNTIME,
            true
        );
    }

    /**
     * Get extension to use for runtime data or configuration cache.
     *
     * @param string $name Runtime data file name (without extension).
     *
     * @return string
     */
    private function getFilename(string $name): string
    {
        //Runtime cache
        return sprintf(
            '%s/%s.%s',
            $this->directory,
            strtolower(str_replace(['/', '\\'], '-', $name)),
            self::EXTENSION
        );
    }
}
