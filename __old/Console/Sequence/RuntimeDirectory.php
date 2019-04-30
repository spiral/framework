<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Console\Sequence;


use Spiral\Boot\DirectoriesInterface;
use Spiral\Files\FilesInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates runtime directory or/and ensure proper permissions for it.
 */
class RuntimeDirectory
{
    /** @var FilesInterface */
    private $files;

    /** @var DirectoriesInterface */
    private $directories;

    /**
     * @param FilesInterface       $files
     * @param DirectoriesInterface $directories
     */
    public function __construct(FilesInterface $files, DirectoriesInterface $directories)
    {
        $this->files = $files;
        $this->directories = $directories;
    }

    /**
     * @param OutputInterface $output
     */
    public function ensure(OutputInterface $output)
    {
        $output->write("Verifying runtime directory... ");

        $runtimeDirectory = $this->directories->get('runtime');

        if (!$this->files->exists($runtimeDirectory)) {
            $this->files->ensureDirectory($runtimeDirectory);
            $output->writeln("<comment>created</comment>");
            return;
        } else {
            $output->writeln("<info>exists</info>");
        }

        foreach ($this->files->getFiles($runtimeDirectory) as $filename) {
            try {
                $this->files->setPermissions($filename, FilesInterface::RUNTIME);
                $this->files->setPermissions(dirname($filename), FilesInterface::RUNTIME);
            } catch (\Throwable $e) {
                $output->writeln(sprintf(
                    "<fg=red>[errored]</fg=red> `%s`: <fg=red>%s</fg=red>",
                    $this->files->relativePath($filename, $runtimeDirectory),
                    $e->getMessage()
                ));
                continue;
            }

            if ($output->isVerbose()) {
                $output->writeln(sprintf(
                    "<fg=green>[updated]</fg=green> `%s`",
                    $this->files->relativePath($filename, $runtimeDirectory)
                ));
            }
        }

        $output->writeln("Runtime directory permissions were updated.");
    }
}