<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\FileDeclaration;
use Spiral\Reactor\Partial\PhpNamespace;
use Spiral\Scaffolder\Config\ScaffolderConfig;

abstract class AbstractDeclaration implements DeclarationInterface
{
    protected FileDeclaration $file;
    protected ClassDeclaration $class;
    protected PhpNamespace $namespace;

    public function __construct(
        protected readonly ScaffolderConfig $config,
        string $name,
        ?string $comment = null,
        ?string $namespace = null,
    ) {
        $this->namespace = new PhpNamespace($namespace ?? $this->config->classNamespace(static::TYPE, $name));

        $this->class = $this->namespace
            ->addClass($this->config->className(static::TYPE, $name))
            ->setComment($comment);

        $this->file = new FileDeclaration();
        $this->file->addNamespace($this->namespace);
        $this->file->setComment($this->config->headerLines());

        $this->declare();
    }

    public function getFile(): FileDeclaration
    {
        return $this->file;
    }

    public function getClass(): ClassDeclaration
    {
        return $this->class;
    }
}
