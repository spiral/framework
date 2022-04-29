<?php

declare(strict_types=1);

namespace Spiral\Views\Loader;

use Spiral\Views\Exception\PathException;
use Spiral\Views\LoaderInterface;

/**
 * Parse view path and return name chunks (namespace, name, basename).
 */
final class PathParser
{
    public function __construct(
        private readonly string $defaultNamespace,
        private readonly string $extension
    ) {
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Check if filename matches to expected extension.
     */
    public function match(string $filename): bool
    {
        $extension = \substr($filename, -\strlen($this->extension) - 1);

        return \strtolower($extension) === \sprintf('.%s', $this->extension);
    }

    /**
     * Parse view path and extract name, namespace and basename information.
     *
     * @throws PathException
     */
    public function parse(string $path): ?ViewPath
    {
        $this->validatePath($path);

        //Cutting extra symbols (see Twig)
        $filename = \preg_replace(
            '#/{2,}#',
            '/',
            \str_replace('\\', '/', $path)
        );

        $namespace = $this->defaultNamespace;
        if (!\str_contains((string) $filename, '.')) {
            //Force default extension
            $filename .= '.' . $this->extension;
        } elseif (!$this->match($filename)) {
            return null;
        }

        if (\str_contains((string) $filename, LoaderInterface::NS_SEPARATOR)) {
            [$namespace, $filename] = explode(LoaderInterface::NS_SEPARATOR, (string) $filename);
        }

        //Twig like namespaces
        if (isset($filename[0]) && $filename[0] === '@') {
            $separator = \strpos($filename, '/');
            if ($separator === false) {
                throw new PathException(\sprintf('Malformed view path "%s" (expecting "@namespace/name").', $path));
            }

            $namespace = \substr($filename, 1, $separator - 1);
            $filename = \substr($filename, $separator + 1);
        }

        return new ViewPath(
            $namespace,
            $this->fetchName($filename),
            $filename
        );
    }

    /**
     * Get view name from given filename.
     */
    public function fetchName(string $filename): string
    {
        return \str_replace('\\', '/', \substr($filename, 0, -1 * (1 + \strlen($this->extension))));
    }

    /**
     * Make sure view filename is OK. Same as in twig.
     *
     * @throws PathException
     */
    private function validatePath(string $path): void
    {
        if (empty($path)) {
            throw new PathException('A view path is empty');
        }

        if (\str_contains($path, "\0")) {
            throw new PathException('A view path cannot contain NUL bytes');
        }

        $path = \ltrim($path, '/');
        $parts = \explode('/', $path);
        $level = 0;

        foreach ($parts as $part) {
            if ('..' === $part) {
                --$level;
            } elseif ('.' !== $part) {
                ++$level;
            }

            if ($level < 0) {
                throw new PathException(\sprintf(
                    'Looks like you try to load a view outside configured directories (%s)',
                    $path
                ));
            }
        }
    }
}
