<?php

declare(strict_types=1);

namespace Spiral\Reactor;

use Spiral\Files\FilesInterface;

class Writer
{
    public function __construct(
        protected FilesInterface $files
    ) {
    }

    public function write(string $filename, FileDeclaration $file): bool
    {
        return $this->files->write(
            filename: $filename,
            data: (new Printer())->print($file),
            ensureDirectory: true
        );
    }
}
