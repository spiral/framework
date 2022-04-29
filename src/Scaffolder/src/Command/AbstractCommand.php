<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Psr\Container\ContainerInterface;
use Spiral\Console\Command;
use Spiral\Core\FactoryInterface;
use Spiral\Files\FilesInterface;
use Spiral\Reactor\ClassDeclaration;
use Spiral\Reactor\FileDeclaration;
use Spiral\Scaffolder\Config\ScaffolderConfig;

abstract class AbstractCommand extends Command
{
    /**
     * Element to be managed.
     */
    protected const ELEMENT = '';

    public function __construct(
        protected ScaffolderConfig $config,
        protected FilesInterface $files,
        ContainerInterface $container,
        private readonly FactoryInterface $factory
    ) {
        $this->setContainer($container);

        parent::__construct();
    }

    protected function createDeclaration(array $parameters = []): ClassDeclaration
    {
        return $this->factory->make(
            $this->declarationClass(static::ELEMENT),
            [
                'name'    => $this->getClass(),
                'comment' => (string)$this->option('comment'),
            ] + $parameters + $this->config->declarationOptions(static::ELEMENT)
        );
    }

    protected function declarationClass(string $element): string
    {
        return $this->config->declarationClass($element);
    }

    /**
     * Get class name of element being rendered.
     */
    protected function getClass(): string
    {
        return $this->config->className(
            static::ELEMENT,
            (string)$this->argument('name')
        );
    }

    /**
     * Write declaration into file.
     *
     * @param string $type If null static::ELEMENT to be used.
     */
    protected function writeDeclaration(ClassDeclaration $declaration, string $type = null): void
    {
        $type ??= static::ELEMENT;

        $filename = $this->config->classFilename($type, (string)$this->argument('name'));
        $filename = $this->files->normalizePath($filename);

        if ($this->files->exists($filename)) {
            $this->writeln(
                \sprintf("<fg=red>Unable to create '<comment>%s</comment>' declaration, ", $declaration->getName())
                . \sprintf("file '<comment>%s</comment>' already exists.</fg=red>", $filename)
            );

            return;
        }

        //File declaration
        $file = new FileDeclaration(
            $this->config->classNamespace($type, (string)$this->argument('name'))
        );

        $file->setDirectives('strict_types=1');
        $file->setComment($this->config->headerLines());
        $file->addElement($declaration);

        $this->files->write(
            $filename,
            $file->render(),
            FilesInterface::READONLY,
            true
        );

        $this->writeln(
            \sprintf("Declaration of '<info>%s</info>' ", $declaration->getName())
            . \sprintf("has been successfully written into '<comment>%s</comment>'.", $filename)
        );
    }

    /**
     * Get namespace of element being rendered.
     */
    protected function getNamespace(): string
    {
        return $this->config->classNamespace(
            static::ELEMENT,
            (string)$this->argument('name')
        );
    }
}
