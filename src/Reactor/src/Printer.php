<?php

declare(strict_types=1);

namespace Spiral\Reactor;

use Nette\PhpGenerator\PsrPrinter;

class Printer
{
    public function print(FileDeclaration $file): string
    {
        return (new PsrPrinter())->printFile($file->getElement());
    }
}
