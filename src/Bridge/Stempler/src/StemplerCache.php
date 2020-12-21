<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler;

use Spiral\Files\Files;
use Spiral\Files\FilesInterface;

final class StemplerCache
{
    /** @var string */
    private $directory;

    /** @var FilesInterface */
    private $files;

    /**
     * @param string         $directory
     * @param FilesInterface $files
     */
    public function __construct(string $directory, FilesInterface $files = null)
    {
        $this->directory = $directory;
        $this->files = $files ?? new Files();
    }

    /**
     * Store template into cache and write invalidation map file.
     *
     * @param string $key
     * @param string $content
     * @param array  $paths
     */
    public function write(string $key, string $content, array $paths = []): void
    {
        // template content
        $this->files->write(
            $this->filename($key),
            $content,
            FilesInterface::RUNTIME,
            true
        );

        // map file
        $this->files->write(
            $this->mapFilename($key),
            sprintf('<?php return %s;', var_export($paths, true)),
            FilesInterface::RUNTIME,
            true
        );
    }

    /**
     * Check if template still fresh (no files used for generation has changed).
     *
     * @param string $key
     * @return bool
     */
    public function isFresh(string $key): bool
    {
        $mapFilename = $this->mapFilename($key);
        if (!$this->files->exists($mapFilename)) {
            return false;
        }

        $time = $this->files->time($this->filename($key));

        $files = (array)include $mapFilename;
        foreach ($files as $file) {
            if ($this->files->time($file) > $time) {
                // some partial has changed
                return false;
            }
        }

        return true;
    }

    /**
     * Delete file from the cache.
     *
     * @param string $key
     */
    public function delete(string $key): void
    {
        $filename = $this->filename($key);
        if ($this->files->exists($filename)) {
            $this->files->delete($filename);
        }

        $mapFilename = $this->mapFilename($key);
        if ($this->files->exists($mapFilename)) {
            $this->files->delete($mapFilename);
        }
    }

    /**
     * Load template content.
     *
     * @param string $key
     */
    public function load(string $key): void
    {
        $filename = $this->filename($key);
        if ($this->files->exists($filename)) {
            include_once $filename;
        }
    }

    /**
     * @param string $key
     * @return string
     */
    private function filename(string $key): string
    {
        return sprintf('%s/%s.php', $this->directory, $key);
    }

    /**
     * @param string $key
     * @return string
     */
    private function mapFilename(string $key): string
    {
        return sprintf('%s/%s-map.php', $this->directory, $key);
    }
}
