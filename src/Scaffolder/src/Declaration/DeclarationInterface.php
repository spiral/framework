<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\FileDeclaration;

interface DeclarationInterface
{
    public const TYPE = '';

    /**
     * This method is called after the declaration class has been created and declares the basic structure of the class.
     */
    public function declare(): void;

    public function getFile(): FileDeclaration;

    public function getClass(): ClassDeclaration;
}
