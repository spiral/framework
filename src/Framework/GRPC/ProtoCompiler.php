<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\GRPC;

use Spiral\Files\FilesInterface;
use Spiral\GRPC\Exception\CompileException;

/**
 * @deprecated since v2.12. Will be removed in v3.0
 * Compiles GRPC protobuf declaration and moves files into proper location.
 */
final class ProtoCompiler
{
    /** @var FilesInterface */
    private $files;

    /** @var string */
    private $basePath;

    /** @var string */
    private $baseNamespace;

    /**
     * @param string         $basePath
     * @param string         $baseNamespace
     * @param FilesInterface $files
     */
    public function __construct(string $basePath, string $baseNamespace, FilesInterface $files)
    {
        $this->basePath = $basePath;
        $this->baseNamespace = str_replace('\\', '/', rtrim($baseNamespace, '\\'));
        $this->files = $files;
    }

    /**
     * @param string $protoFile
     * @return array
     *
     * @throws CompileException
     */
    public function compile(string $protoFile): array
    {
        $tmpDir = $this->tmpDir();

        exec(
            sprintf(
                'protoc --php_out=%s --php-grpc_out=%s -I %s %s 2>&1',
                escapeshellarg($tmpDir),
                escapeshellarg($tmpDir),
                escapeshellarg(dirname($protoFile)),
                implode(' ', array_map('escapeshellarg', $this->getProtoFiles($protoFile)))
            ),
            $output
        );

        $output = trim(implode("\n", $output), "\n ,");

        if ($output !== '') {
            $this->files->deleteDirectory($tmpDir);
            throw new CompileException($output);
        }

        // copying files (using relative path and namespace)
        $result = [];
        foreach ($this->files->getFiles($tmpDir) as $file) {
            $result[] = $this->copy($tmpDir, $file);
        }

        $this->files->deleteDirectory($tmpDir);

        return $result;
    }

    /**
     * @param string $tmpDir
     * @param string $file
     * @return string
     */
    private function copy(string $tmpDir, string $file): string
    {
        $source = ltrim($this->files->relativePath($file, $tmpDir), '\\/');
        if (strpos($source, $this->baseNamespace) === 0) {
            $source = ltrim(substr($source, strlen($this->baseNamespace)), '\\/');
        }

        $target = $this->files->normalizePath($this->basePath . '/' . $source);

        $this->files->ensureDirectory(dirname($target));
        $this->files->copy($file, $target);

        return $target;
    }

    /**
     * @return string
     */
    private function tmpDir(): string
    {
        $directory = sys_get_temp_dir() . '/' . spl_object_hash($this);
        $this->files->ensureDirectory($directory);

        return $this->files->normalizePath($directory, true);
    }

    /**
     * Include all proto files from the directory.
     *
     * @param string $protoFile
     * @return array
     */
    private function getProtoFiles(string $protoFile): array
    {
        return array_filter(
            $this->files->getFiles(dirname($protoFile)),
            function ($file) {
                return strpos($file, '.proto') !== false;
            }
        );
    }
}
