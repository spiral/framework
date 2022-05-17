<?php

declare(strict_types=1);

namespace Spiral\Reactor;

use Nette\PhpGenerator\PsrPrinter;
use Spiral\Files\FilesInterface;

class Printer
{
    public function __construct(
        protected FilesInterface $files
    ) {
    }

    public function print(FileDeclaration $file): string
    {
        return (new PsrPrinter())->printFile($file->getElement());
    }

    public function write(string $filename, FileDeclaration $file): bool
    {
        return $this->files->write(
            filename: $filename,
            data: $this->print($file),
            ensureDirectory: true
        );
    }
}
