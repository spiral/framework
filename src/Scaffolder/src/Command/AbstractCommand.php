<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 * @author  Valentin V (vvval)
 */

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

    /**
     * @var ScaffolderConfig
     */
    protected $config;

    /**
     * @var FilesInterface
     */
    protected $files;

    /** @var FactoryInterface */
    private $factory;

    /**
     * @param ScaffolderConfig   $config
     * @param FilesInterface     $files
     * @param ContainerInterface $container
     * @param FactoryInterface   $factory
     */
    public function __construct(
        ScaffolderConfig $config,
        FilesInterface $files,
        ContainerInterface $container,
        FactoryInterface $factory
    ) {
        $this->config = $config;
        $this->files = $files;
        $this->factory = $factory;
        $this->setContainer($container);

        parent::__construct();
    }

    /**
     * @param array $parameters
     * @return ClassDeclaration
     */
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

    /**
     * @param string $element
     * @return string
     */
    protected function declarationClass(string $element): string
    {
        return $this->config->declarationClass($element);
    }

    /**
     * Get class name of element being rendered.
     *
     * @return string
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
     * @param ClassDeclaration $declaration
     * @param string           $type If null static::ELEMENT to be used.
     */
    protected function writeDeclaration(ClassDeclaration $declaration, string $type = null): void
    {
        $type = $type ?? static::ELEMENT;

        $filename = $this->config->classFilename($type, (string)$this->argument('name'));
        $filename = $this->files->normalizePath($filename);

        if ($this->files->exists($filename)) {
            $this->writeln(
                "<fg=red>Unable to create '<comment>{$declaration->getName()}</comment>' declaration, "
                . "file '<comment>{$filename}</comment>' already exists.</fg=red>"
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
            "Declaration of '<info>{$declaration->getName()}</info>' "
            . "has been successfully written into '<comment>{$filename}</comment>'."
        );
    }

    /**
     * Get namespace of element being rendered.
     *
     * @return string
     */
    protected function getNamespace(): string
    {
        return $this->config->classNamespace(
            static::ELEMENT,
            (string)$this->argument('name')
        );
    }
}
