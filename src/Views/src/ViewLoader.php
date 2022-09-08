<?php

declare(strict_types=1);

namespace Spiral\Views;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Views\Event\ViewNotFound;
use Spiral\Views\Exception\LoaderException;
use Spiral\Views\Loader\PathParser;
use Spiral\Views\Loader\ViewPath;

/**
 * Loads and locates view files associated with specific extensions.
 */
final class ViewLoader implements LoaderInterface
{
    private ?PathParser $parser = null;
    private readonly FilesInterface $files;

    public function __construct(
        private readonly array $namespaces,
        FilesInterface $files = null,
        private readonly string $defaultNamespace = self::DEFAULT_NAMESPACE,
        private readonly ?EventDispatcherInterface $dispatcher = null,
    ) {
        $this->files = $files ?? new Files();
    }

    public function withExtension(string $extension): LoaderInterface
    {
        $loader = clone $this;
        $loader->parser = new PathParser($this->defaultNamespace, $extension);

        return $loader;
    }

    public function getExtension(): ?string
    {
        if ($this->parser !== null) {
            return $this->parser->getExtension();
        }

        return null;
    }

    /**
     * @psalm-assert-if-true non-empty-string $filename
     * @psalm-assert-if-true ViewPath $parsed
     */
    public function exists(string $path, string &$filename = null, ViewPath &$parsed = null): bool
    {
        if (empty($this->parser)) {
            throw new LoaderException('Unable to locate view source, no extension has been associated.');
        }

        $parsed = $this->parser->parse($path);
        if ($parsed === null) {
            return false;
        }

        if (!isset($this->namespaces[$parsed->getNamespace()])) {
            return false;
        }

        foreach ((array)$this->namespaces[$parsed->getNamespace()] as $directory) {
            $directory = $this->files->normalizePath($directory, true);
            if ($this->files->exists(\sprintf('%s%s', $directory, $parsed->getBasename()))) {
                $filename = \sprintf('%s%s', $directory, $parsed->getBasename());

                return true;
            }
        }

        return false;
    }

    public function load(string $path): ViewSource
    {
        if (!$this->exists($path, $filename, $parsed)) {
            $this->dispatcher?->dispatch(new ViewNotFound($path));

            throw new LoaderException(\sprintf('Unable to load view `%s`, file does not exist.', $path));
        }

        return new ViewSource(
            $filename,
            $parsed->getNamespace(),
            $parsed->getName()
        );
    }

    public function list(string $namespace = null): array
    {
        if (empty($this->parser)) {
            throw new LoaderException('Unable to list view sources, no extension has been associated.');
        }

        $result = [];
        foreach ($this->namespaces as $ns => $directories) {
            if (!empty($namespace) && $namespace != $ns) {
                continue;
            }

            foreach ((array)$directories as $directory) {
                $files = $this->files->getFiles($directory);

                foreach ($files as $filename) {
                    if (!$this->parser->match($filename)) {
                        // does not belong to this loader
                        continue;
                    }

                    $name = $this->parser->fetchName($this->files->relativePath($filename, $directory));
                    $result[] = \sprintf('%s%s%s', $ns, self::NS_SEPARATOR, $name);
                }
            }
        }

        return $result;
    }
}
