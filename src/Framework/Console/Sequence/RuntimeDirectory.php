<?php

declare(strict_types=1);

namespace Spiral\Console\Sequence;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Files\FilesInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates runtime directory or/and ensure proper permissions for it.
 */
final class RuntimeDirectory
{
    public function __construct(
        private readonly FilesInterface $files,
        private readonly DirectoriesInterface $dirs
    ) {
    }

    public function ensure(OutputInterface $output): void
    {
        $output->write('Verifying runtime directory... ');

        $runtimeDirectory = $this->dirs->get('runtime');

        if (!$this->files->exists($runtimeDirectory)) {
            $this->files->ensureDirectory($runtimeDirectory);
            $output->writeln('<comment>created</comment>');
            return;
        }
        $output->writeln('<info>exists</info>');


        foreach ($this->files->getFiles($runtimeDirectory) as $filename) {
            try {
                $this->files->setPermissions($filename, FilesInterface::RUNTIME);
                $this->files->setPermissions(\dirname($filename), FilesInterface::RUNTIME);
            } catch (\Throwable $e) {
                // @codeCoverageIgnoreStart
                $output->writeln(
                    \sprintf(
                        '<fg=red>[errored]</fg=red> `%s`: <fg=red>%s</fg=red>',
                        $this->files->relativePath($filename, $runtimeDirectory),
                        $e->getMessage()
                    )
                );
                continue;
                // @codeCoverageIgnoreEnd
            }

            if ($output->isVerbose()) {
                $output->writeln(
                    \sprintf(
                        '<fg=green>[updated]</fg=green> `%s`',
                        $this->files->relativePath($filename, $runtimeDirectory)
                    )
                );
            }
        }

        $output->writeln('Runtime directory permissions were updated.');
    }
}
