<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Command\GRPC;

use Codedungeon\PHPCliColors\Color;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\KernelInterface;
use Spiral\Console\Command;
use Spiral\Files\FilesInterface;
use Spiral\GRPC\ProtoCompiler;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @deprecated since v2.12. Will be removed in v3.0
 */
final class GenerateCommand extends Command
{
    protected const NAME        = 'grpc:generate';
    protected const DESCRIPTION = 'Generate GPRC service code using protobuf specification';
    protected const ARGUMENTS   = [
        ['proto', InputArgument::REQUIRED, 'Protobuf specification file'],
        ['path', InputArgument::OPTIONAL, 'Base path for generated service code', 'auto'],
        ['namespace', InputArgument::OPTIONAL, 'Base namespace for generated service code', 'auto'],
    ];

    /**
     * @param KernelInterface      $kernel
     * @param FilesInterface       $files
     * @param DirectoriesInterface $dirs
     */
    public function perform(KernelInterface $kernel, FilesInterface $files, DirectoriesInterface $dirs): void
    {
        $protoFile = $this->argument('proto');
        if (!file_exists($protoFile)) {
            $this->sprintf('<error>File `%s` not found.</error>', $protoFile);
            return;
        }

        $compiler = new ProtoCompiler(
            $this->getPath($kernel),
            $this->getNamespace($kernel),
            $files
        );

        $this->sprintf("<info>Compiling <fg=cyan>`%s`</fg=cyan>:</info>\n", $protoFile);

        try {
            $result = $compiler->compile($protoFile);
        } catch (\Throwable $e) {
            $this->sprintf("<error>Error:</error> <fg=red>%s</fg=red>\n", $e->getMessage());
            return;
        }

        if ($result === []) {
            $this->sprintf("<info>No files were generated.</info>\n", $protoFile);
            return;
        }

        foreach ($result as $file) {
            $this->sprintf(
                "<fg=green>•</fg=green> %s%s%s\n",
                Color::LIGHT_WHITE,
                $files->relativePath($file, $dirs->get('root')),
                Color::RESET
            );
        }
    }

    /**
     * Get or detect base source code path. By default fallbacks to kernel location.
     *
     * @param KernelInterface $kernel
     * @return string
     */
    protected function getPath(KernelInterface $kernel): string
    {
        $path = $this->argument('path');
        if ($path !== 'auto') {
            return $path;
        }

        $r = new \ReflectionObject($kernel);

        return dirname($r->getFileName());
    }

    /**
     * Get or detect base namespace. By default fallbacks to kernel namespace.
     *
     * @param KernelInterface $kernel
     * @return string
     */
    protected function getNamespace(KernelInterface $kernel): string
    {
        $namespace = $this->argument('namespace');
        if ($namespace !== 'auto') {
            return $namespace;
        }

        $r = new \ReflectionObject($kernel);

        return $r->getNamespaceName();
    }
}
