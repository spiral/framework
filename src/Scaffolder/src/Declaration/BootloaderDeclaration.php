<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\BootloadManager\Methods;

class BootloaderDeclaration extends AbstractDeclaration
{
    public const TYPE = 'bootloader';

    /**
     * Declare constants and boot method.
     */
    public function declare(): void
    {
        $this->namespace->addUse(Bootloader::class);

        $this->class->setExtends(Bootloader::class);

        $this->class->addConstant('BINDINGS', [])->setProtected();
        $this->class->addConstant('SINGLETONS', [])->setProtected();
        $this->class->addConstant('DEPENDENCIES', [])->setProtected();

        $this->class->addMethod(Methods::INIT->value)->setReturnType('void');
        $this->class->addMethod(Methods::BOOT->value)->setReturnType('void');
    }
}
