<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Psr\Container\ContainerInterface;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Console\Command;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\FactoryInterface;
use Spiral\Files\FilesInterface;
use Spiral\Reactor\Writer;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Scaffolder\Declaration\DeclarationInterface;
use Spiral\Scaffolder\Declaration\HasInstructions;

abstract class AbstractCommand extends Command
{
    public function __construct(
        protected ScaffolderConfig $config,
        protected FilesInterface $files,
        #[Proxy] ContainerInterface $container,
        private readonly FactoryInterface $factory,
        private readonly DirectoriesInterface $dirs,
    ) {
        $this->setContainer($container);

        parent::__construct();
    }

    /**
     * @template TClass of DeclarationInterface
     *
     * @param class-string<TClass> $class
     * @return TClass
     */
    protected function createDeclaration(string $class, array $params = []): DeclarationInterface
    {
        return $this->factory->make(
            $class,
            [
                'name' => (string)$this->argument('name'),
                'comment' => $this->getComment(),
                'namespace' => $this->getNamespace(),
            ] + $params + $this->config->declarationOptions($class::TYPE),
        );
    }

    /**
     * Write declaration into file.
     */
    protected function writeDeclaration(DeclarationInterface $declaration): void
    {
        $filename = $this->config->classFilename(
            $declaration::TYPE,
            (string)$this->argument('name'),
            $this->getNamespace(),
        );

        $rootDirectory = $this->dirs->get('root');

        $className = $declaration->getClass()->getName();
        $relativeFilename = \str_replace($rootDirectory, '', $filename);

        if ($this->files->exists($filename)) {
            $this->writeln(
                \sprintf("<fg=red>Unable to create '<comment>%s</comment>' declaration, ", $className)
                . \sprintf("file '<comment>%s</comment>' already exists.</fg=red>", $relativeFilename),
            );

            return;
        }

        //File declaration
        (new Writer($this->files))->write($filename, $declaration->getFile());

        $this->writeln(
            \sprintf("Declaration of '<info>%s</info>' ", $className)
            . \sprintf("has been successfully written into '<comment>%s</comment>'.", $relativeFilename),
        );

        if ($declaration instanceof HasInstructions && \count($declaration->getInstructions()) > 0) {
            $this->newLine();
            $this->writeln('<fg=green>Next steps:</fg=green>');

            foreach ($declaration->getInstructions() as $i => $instruction) {
                $this->writeln(\sprintf('%d. %s', (string)(++$i), $instruction));
            }
        }
    }

    protected function getNamespace(): ?string
    {
        return $this->hasOption('namespace') ? $this->option('namespace') : null;
    }

    protected function getComment(): ?string
    {
        return $this->hasOption('comment') ? $this->option('comment') : null;
    }
}
