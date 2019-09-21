<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Command;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Console\Command;
use Spiral\Files\FilesInterface;
use Spiral\Module\Exception\PublishException;
use Spiral\Module\Publisher;
use Spiral\Module\PublisherInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Target path can be in form "@cache/path".
 */
final class PublishCommand extends Command
{
    protected const NAME        = 'publish';
    protected const DESCRIPTION = 'Publish resources';

    /**
     * {@inheritdoc}
     */
    public const ARGUMENTS = [
        ['type', InputArgument::REQUIRED, 'Operation type [replace|follow|ensure]'],
        ['target', InputArgument::REQUIRED, 'Target file or directory'],
        ['source', InputArgument::OPTIONAL, 'Source file or directory'],
        ['mode', InputArgument::OPTIONAL, 'runtime', 'File mode [readonly|runtime]'],
    ];

    /**
     * @param string|null $name
     */
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->setHidden(true);
    }

    /**
     * @param Publisher            $publisher
     * @param FilesInterface       $files
     * @param DirectoriesInterface $directories
     */
    public function perform(
        Publisher $publisher,
        FilesInterface $files,
        DirectoriesInterface $directories
    ): void {
        switch ($this->argument('type')) {
            case 'replace':
            case 'follow':
                if ($this->isDirectory()) {
                    $this->sprintf(
                        '<fg=cyan>•</fg=cyan> publish directory <comment>%s</comment> to <comment>%s</comment>',
                        $this->getSource($files, $directories),
                        $this->getTarget($files, $directories)
                    );

                    $publisher->publishDirectory(
                        $this->getSource($files, $directories),
                        $this->getTarget($files, $directories),
                        $this->getMergeMode(),
                        $this->getFileMode()
                    );
                } else {
                    $this->sprintf(
                        '<fg=cyan>•</fg=cyan> publish file <comment>%s</comment> to <comment>%s</comment>',
                        $this->getSource($files, $directories),
                        $this->getTarget($files, $directories)
                    );

                    $publisher->publish(
                        $this->getSource($files, $directories),
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
                throw new PublishException("Invalid public operation `{$this->argument('type')}`.");
        }
    }

    /**
     * @param FilesInterface       $files
     * @param DirectoriesInterface $directories
     * @return null|string
     */
    private function getSource(FilesInterface $files, DirectoriesInterface $directories): ?string
    {
        if (!$this->isDirectory()) {
            return $files->normalizePath($this->argument('source'));
        }

        return $files->normalizePath(rtrim($this->argument('source'), '/*'), true);
    }

    /**
     * @param FilesInterface       $files
     * @param DirectoriesInterface $directories
     * @return null|string
     */
    private function getTarget(FilesInterface $files, DirectoriesInterface $directories): ?string
    {
        $target = $this->argument('target');
        foreach ($directories->getAll() as $alias => $value) {
            $target = str_replace("@{$alias}", $value, $target);
        }

        return $files->normalizePath($target);
    }

    /**
     * @return bool
     */
    private function isDirectory(): bool
    {
        if ($this->argument('type') == 'ensure') {
            return true;
        }

        if (strpos($this->argument('source'), '*') !== false) {
            return true;
        }

        return is_dir($this->argument('source'));
    }

    /**
     * @return string
     */
    private function getMergeMode(): string
    {
        switch ($this->argument('type')) {
            case 'follow':
                return PublisherInterface::FOLLOW;
            case 'replace':
                return PublisherInterface::REPLACE;
        }

        throw new PublishException("Undefined merge mode `{$this->argument('type')}`");
    }

    /**
     * @return int
     */
    private function getFileMode(): int
    {
        switch ($this->argument('mode')) {
            case 'readonly':
                return FilesInterface::READONLY;
            case 'runtime':
                return FilesInterface::RUNTIME;
            default:
                return FilesInterface::RUNTIME;
        }
    }
}
