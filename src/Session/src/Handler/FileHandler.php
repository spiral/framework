<?php

declare(strict_types=1);

namespace Spiral\Session\Handler;

use Spiral\Files\FilesInterface;

/**
 * Stores session data in file.
 */
final class FileHandler implements \SessionHandlerInterface
{
    public function __construct(
        private readonly FilesInterface $files,
        private readonly string $directory
    ) {
    }

    public function close(): bool
    {
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public function destroy(string $id): bool
    {
        return $this->files->delete($this->getFilename($id));
    }

    /**
     * @codeCoverageIgnore
     * @psalm-suppress ParamNameMismatch
     */
    public function gc(int $maxlifetime): int
    {
        foreach ($this->files->getFiles($this->directory) as $filename) {
            if ($this->files->time($filename) < \time() - $maxlifetime) {
                $this->files->delete($filename);
            }
        }

        return $maxlifetime;
    }

    /**
     * @psalm-suppress ParamNameMismatch
     */
    public function open(string $path, string $id): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        return $this->files->exists($this->getFilename($id)) ? $this->files->read($this->getFilename($id)) : '';
    }

    public function write(string $id, string $data): bool
    {
        return $this->files->write($this->getFilename($id), $data, FilesInterface::RUNTIME, true);
    }

    /**
     * Session data filename.
     */
    protected function getFilename($id): string
    {
        return \sprintf('%s/%s', $this->directory, $id);
    }
}
