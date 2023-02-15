<?php

declare(strict_types=1);

namespace Spiral\Command;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Console\Attribute\Question;
use Spiral\Console\Command;
use Spiral\Files\FilesInterface;
use Spiral\Module\Exception\PublishException;
use Spiral\Module\Publisher;
use Spiral\Module\PublisherInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Target path can be in form "@cache/path".
 */
#[Question(
    question: 'What type of operation would you like to perform? You can choose from `replace`, `follow`, or `ensure`.',
    argument: 'type'
)]
#[Question(
    question: 'What is the path to the directory where you want to publish resources?',
    argument: 'target'
)]
final class PublishCommand extends Command
{
    protected const NAME        = 'publish';
    protected const DESCRIPTION = 'Publish resources';
    protected const ARGUMENTS   = [
        ['type', InputArgument::REQUIRED, 'Operation type [replace|follow|ensure]'],
        ['target', InputArgument::REQUIRED, 'Target file or directory'],
        ['source', InputArgument::OPTIONAL, 'Source file or directory'],
        ['mode', InputArgument::OPTIONAL, 'runtime', 'File mode [readonly|runtime]'],
    ];

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->setHidden(true);
    }

    public function perform(
        Publisher $publisher,
        FilesInterface $files,
        DirectoriesInterface $directories
    ): int {
        switch ($this->argument('type')) {
            case 'replace':
            case 'follow':
                if ($this->isDirectory()) {
                    $this->sprintf(
                        '<fg=cyan>•</fg=cyan> publish directory <comment>%s</comment> to <comment>%s</comment>',
                        $this->getSource($files),
                        $this->getTarget($files, $directories)
                    );

                    $publisher->publishDirectory(
                        $this->getSource($files),
                        $this->getTarget($files, $directories),
                        $this->getMergeMode(),
                        $this->getFileMode()
                    );
                } else {
                    $this->sprintf(
                        '<fg=cyan>•</fg=cyan> publish file <comment>%s</comment> to <comment>%s</comment>',
                        $this->getSource($files),
                        $this->getTarget($files, $directories)
                    );

                    $publisher->publish(
                        $this->getSource($files),
                        $this->getTarget($files, $directories),
                        $this->getMergeMode(),
                        $this->getFileMode()
                    );
                }

                break;
            case 'ensure':
                $this->sprintf(
                    '<fg=cyan>•</fg=cyan> ensure directory <comment>%s</comment>',
                    $this->getTarget($files, $directories)
                );

                $publisher->ensureDirectory(
                    $this->getTarget($files, $directories),
                    $this->getFileMode()
                );

                break;
            default:
                throw new PublishException(\sprintf('Invalid public operation `%s`.', $this->argument('type')));
        }

        return self::SUCCESS;
    }

    private function getSource(FilesInterface $files): ?string
    {
        if (!$this->isDirectory()) {
            return $files->normalizePath($this->argument('source'));
        }

        return $files->normalizePath(\rtrim($this->argument('source'), '/*'), true);
    }

    private function getTarget(FilesInterface $files, DirectoriesInterface $directories): ?string
    {
        $target = $this->argument('target');
        foreach ($directories->getAll() as $alias => $value) {
            $target = \str_replace(\sprintf('@%s', $alias), $value, $target);
        }

        return $files->normalizePath($target);
    }

    private function isDirectory(): bool
    {
        return match (true) {
            $this->argument('type') === 'ensure' => true,
            \str_contains((string) $this->argument('source'), '*') => true,
            default => \is_dir($this->argument('source'))
        };
    }

    private function getMergeMode(): string
    {
        return match ($this->argument('type')) {
            'follow' => PublisherInterface::FOLLOW,
            'replace' => PublisherInterface::REPLACE,
            default => throw new PublishException(\sprintf('Undefined merge mode `%s`', $this->argument('type'))),
        };
    }

    private function getFileMode(): int
    {
        return match ($this->argument('mode')) {
            'readonly' => FilesInterface::READONLY,
            'runtime' => FilesInterface::RUNTIME,
            default => FilesInterface::RUNTIME,
        };
    }
}
