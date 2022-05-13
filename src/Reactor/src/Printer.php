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

    public function print(string $filename, FileDeclaration $file): bool
    {
        $printer = new PsrPrinter();

        return $this->files->write(
            filename: $filename,
            data: $printer->printFile($file->getElement()),
            ensureDirectory: true
        );
    }
}
