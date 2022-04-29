<?php

declare(strict_types=1);

namespace Spiral\Stempler;

use Spiral\Files\Files;
use Spiral\Files\FilesInterface;

final class StemplerCache
{
    public function __construct(
        private readonly string $directory,
        private readonly FilesInterface $files = new Files()
    ) {
    }

    /**
     * Store template into cache and write invalidation map file.
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
            \sprintf('<?php return %s;', \var_export($paths, true)),
            FilesInterface::RUNTIME,
            true
        );
    }

    /**
     * Check if template still fresh (no files used for generation has changed).
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
     */
    public function load(string $key): void
    {
        $filename = $this->filename($key);
        if ($this->files->exists($filename)) {
            include_once $filename;
        }
    }

    private function filename(string $key): string
    {
        return \sprintf('%s/%s.php', $this->directory, $key);
    }

    private function mapFilename(string $key): string
    {
        return \sprintf('%s/%s-map.php', $this->directory, $key);
    }
}
