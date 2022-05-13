<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Psr\Container\ContainerInterface;
use Spiral\Console\Command;
use Spiral\Core\FactoryInterface;
use Spiral\Files\FilesInterface;
use Spiral\Reactor\Printer;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Scaffolder\Declaration\DeclarationInterface;

abstract class AbstractCommand extends Command
{
    public function __construct(
        protected ScaffolderConfig $config,
        protected FilesInterface $files,
        ContainerInterface $container,
        private readonly FactoryInterface $factory
    ) {
        $this->setContainer($container);

        parent::__construct();
    }

    protected function createDeclaration(string $class): DeclarationInterface
    {
        return $this->factory->make(
            $class,
            [
                'name'    => (string) $this->argument('name'),
                'comment' => $this->option('comment'),
            ] + $this->config->declarationOptions($class::TYPE)
        );
    }

    /**
     * Write declaration into file.
     */
    protected function writeDeclaration(DeclarationInterface $declaration): void
    {
        $filename = $this->config->classFilename($declaration::TYPE, (string) $this->argument('name'));
        $filename = $this->files->normalizePath($filename);
        $className = $declaration->getClass()->getName();

        if ($this->files->exists($filename)) {
            $this->writeln(
                \sprintf("<fg=red>Unable to create '<comment>%s</comment>' declaration, ", $className)
                . \sprintf("file '<comment>%s</comment>' already exists.</fg=red>", $filename)
            );

            return;
        }

        //File declaration
        (new Printer($this->files))->print($filename, $declaration->getFile());

        $this->writeln(
            \sprintf("Declaration of '<info>%s</info>' ", $className)
            . \sprintf("has been successfully written into '<comment>%s</comment>'.", $filename)
        );
    }
}
