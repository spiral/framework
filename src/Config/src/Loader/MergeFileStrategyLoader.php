<?php

declare(strict_types=1);

namespace Spiral\Config\Loader;

use Spiral\Config\Exception\LoaderException;
use Spiral\Config\LoaderInterface;

/**
 * @internal
 *
 * Load all files with the same name from all provided directories and merge them using the provided
 * merge strategy.
 */
final class MergeFileStrategyLoader implements LoaderInterface
{
    public function __construct(
        private readonly DirectoriesRepositoryInterface $directories,
        private readonly FileLoaderRegistry $fileLoader,
        private readonly ConfigsMergerInterface $configsMerger,
    ) {}

    public function has(string $section): bool
    {
        foreach ($this->fileLoader->getExtensions() as $extension) {
            if ($this->findFiles($section, $extension) !== []) {
                return true;
            }
        }

        return false;
    }

    public function load(string $section): array
    {
        $files = [];

        foreach ($this->fileLoader->getExtensions() as $extension) {
            $foundFiles = $this->findFiles($section, $extension);

            if ($foundFiles === []) {
                continue;
            }

            if (!isset($files[$extension])) {
                $files[$extension] = [];
            }

            $files[$extension] = [...$files[$extension], ...$foundFiles];
        }

        if ($files === []) {
            throw new LoaderException(\sprintf('Unable to load config `%s`: no suitable loader found.', $section));
        }

        $configs = [];
        foreach ($files as $ext => $_files) {
            foreach ($_files as $file) {
                try {
                    $configs[] = $this->fileLoader->getLoader($ext)->loadFile($section, $file);
                } catch (LoaderException $e) {
                    throw new LoaderException(
                        "Unable to load config `{$section}`: {$e->getMessage()}",
                        $e->getCode(),
                        $e,
                    );
                }
            }
        }

        return $this->configsMerger->merge(...$configs);
    }

    private function findFiles(string $section, string $extension): array
    {
        $files = [];
        foreach ($this->directories as $directory) {
            $filename = \sprintf('%s/%s.%s', $directory, $section, $extension);
            if (\file_exists($filename)) {
                $files[] = $filename;
            }
        }

        return $files;
    }
}
