<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Config;

use Doctrine\Inflector\Rules\English\InflectorFactory;
use Spiral\Core\InjectableConfig;
use Spiral\Scaffolder\Exception\ScaffolderException;

/**
 * Configuration for default scaffolder namespaces and other rendering options.
 */
class ScaffolderConfig extends InjectableConfig
{
    public const CONFIG = 'scaffolder';

    protected array $config = [
        'header' => [],
        'directory' => '',
        'namespace' => '',
        'declarations' => [],
        'defaults' => [
            'declarations' => [],
        ],
    ];

    public function headerLines(): array
    {
        return $this->config['header'];
    }

    /**
     * @deprecated since v3.8.0.
     */
    public function baseDirectory(): string
    {
        return $this->config['directory'];
    }

    /**
     * @return non-empty-string[]
     */
    public function getDeclarations(): array
    {
        return \array_keys($this->config['defaults']['declarations'] ?? []) + \array_keys(
            $this->config['declarations'],
        );
    }

    /**
     * @param non-empty-string $element
     */
    public function declarationDirectory(string $element): string
    {
        $declaration = $this->getDeclaration($element);

        return !empty($declaration['directory']) ? $declaration['directory'] : $this->config['directory'];
    }

    /**
     * @param non-empty-string $element
     */
    public function className(string $element, string $name): string
    {
        ['name' => $name] = $this->parseName($name);

        $class = $this->classify($name);
        $postfix = $this->elementPostfix($element);

        return \str_ends_with($class, $postfix) ? $class : $class . $postfix;
    }

    /**
     * @param non-empty-string $element
     */
    public function classNamespace(string $element, string $name = ''): string
    {
        $localNamespace = \trim((string) $this->getOption($element, 'namespace', ''), '\\');
        ['namespace' => $namespace] = $this->parseName($name);

        if (!empty($namespace)) {
            $localNamespace .= '\\' . $this->classify($namespace);
        }

        if (empty($this->baseNamespace($element))) {
            return $localNamespace;
        }

        return \trim($this->baseNamespace($element) . '\\' . $localNamespace, '\\');
    }

    /**
     * @param non-empty-string $element
     * @param non-empty-string $name
     *
     * @return non-empty-string
     */
    public function classFilename(string $element, string $name, ?string $namespace = null): string
    {
        $elementNamespace = $namespace ?? $this->classNamespace($element, $name);
        $elementNamespace = \substr($elementNamespace, \strlen($this->baseNamespace($element)));

        return $this->joinPathChunks([
            $this->declarationDirectory($element),
            \str_replace('\\', '/', $elementNamespace),
            $this->className($element, $name) . '.php',
        ], '/');
    }

    /**
     * @param non-empty-string $element
     *
     * @throws ScaffolderException
     */
    public function declarationClass(string $element): string
    {
        $class = $this->getOption($element, 'class');

        if (empty($class)) {
            throw new ScaffolderException(
                \sprintf("Unable to scaffold '%s', no declaration class found", $element),
            );
        }

        return $class;
    }

    /**
     * Declaration options.
     *
     * @param non-empty-string $element
     */
    public function declarationOptions(string $element): array
    {
        return $this->getOption($element, 'options', []);
    }

    /**
     * Get declaration options by element name.
     *
     * @param non-empty-string $element
     */
    public function getDeclaration(string $element): array
    {
        $default = $this->config['defaults']['declarations'][$element] ?? [];
        $declaration = $this->config['declarations'][$element] ?? [];

        return $declaration + $default;
    }

    /**
     * @param non-empty-string $element
     */
    private function elementPostfix(string $element): string
    {
        return $this->getOption($element, 'postfix', '');
    }

    /**
     * @param non-empty-string $element
     * @param non-empty-string $section
     */
    private function getOption(string $element, string $section, mixed $default = null): mixed
    {
        $declaration = $this->getDeclaration($element);

        if ($declaration === []) {
            throw new ScaffolderException(\sprintf("Undefined declaration '%s'.", $element));
        }

        if (\array_key_exists($section, $declaration)) {
            return $declaration[$section];
        }

        return $default;
    }

    /**
     * Split user name into namespace and class name.
     *
     * @param non-empty-string $name
     *
     * @return array{namespace: string, name: non-empty-string}
     */
    private function parseName(string $name): array
    {
        $name = \str_replace('/', '\\', $name);

        if (str_contains($name, '\\')) {
            $names = \explode('\\', $name);
            $class = \array_pop($names);

            return ['namespace' => \implode('\\', $names), 'name' => $class];
        }

        //No user namespace
        return ['namespace' => '', 'name' => $name];
    }

    /**
     * @param non-empty-string $element
     */
    private function baseNamespace(string $element): string
    {
        $declaration = $this->getDeclaration($element);

        if (\array_key_exists('baseNamespace', $declaration)) {
            return \trim((string)$this->getOption($element, 'baseNamespace', ''), '\\');
        }

        return \trim((string) $this->config['namespace'], '\\');
    }

    private function joinPathChunks(array $chunks, string $joint): string
    {
        $firstChunkIterated = false;
        $joinedPath = '';
        foreach ($chunks as $chunk) {
            if (!$firstChunkIterated) {
                $firstChunkIterated = true;
                $joinedPath = $chunk;
            } else {
                $joinedPath = \rtrim((string) $joinedPath, $joint) . $joint . \ltrim((string) $chunk, $joint);
            }
        }

        return $joinedPath;
    }

    private function classify(string $name): string
    {
        return (new InflectorFactory())
            ->build()
            ->classify($name);
    }
}
