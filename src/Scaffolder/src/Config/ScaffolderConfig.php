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
        'header'       => [],
        'directory'    => '',
        'namespace'    => '',
        'declarations' => [],
    ];

    public function headerLines(): array
    {
        return $this->config['header'];
    }

    public function baseDirectory(): string
    {
        return $this->config['directory'];
    }

    public function className(string $element, string $name): string
    {
        ['name' => $name] = $this->parseName($name);

        $class = $this->classify($name);
        $postfix = $this->elementPostfix($element);

        return \str_ends_with($class, $postfix) ? $class : $class . $postfix;
    }

    public function classNamespace(string $element, string $name = ''): string
    {
        $localNamespace = \trim($this->getOption($element, 'namespace', ''), '\\');
        ['namespace' => $namespace] = $this->parseName($name);

        if (!empty($namespace)) {
            $localNamespace .= '\\' . $this->classify($namespace);
        }

        if (empty($this->baseNamespace($element))) {
            return $localNamespace;
        }

        return \trim($this->baseNamespace($element) . '\\' . $localNamespace, '\\');
    }

    public function classFilename(string $element, string $name, ?string $namespace = null): string
    {
        $elementNamespace = $namespace ?? $this->classNamespace($element, $name);
        $elementNamespace = \substr($elementNamespace, \strlen($this->baseNamespace($element)));

        return $this->joinPathChunks([
            $this->baseDirectory(),
            \str_replace('\\', '/', $elementNamespace),
            $this->className($element, $name) . '.php',
        ], '/');
    }

    /**
     * @throws ScaffolderException
     */
    public function declarationClass(string $element): string
    {
        $class = $this->getOption($element, 'class');

        if (empty($class)) {
            throw new ScaffolderException(
                \sprintf("Unable to scaffold '%s', no declaration class found", $element)
            );
        }

        return $class;
    }

    /**
     * Declaration options.
     */
    public function declarationOptions(string $element): array
    {
        return $this->getOption($element, 'options', []);
    }

    private function elementPostfix(string $element): string
    {
        return $this->getOption($element, 'postfix', '');
    }

    private function getOption(string $element, string $section, mixed $default = null): mixed
    {
        if (!isset($this->config['declarations'][$element])) {
            throw new ScaffolderException(\sprintf("Undefined declaration '%s'.", $element));
        }

        if (\array_key_exists($section, $this->config['declarations'][$element])) {
            return $this->config['declarations'][$element][$section];
        }

        return $default;
    }

    /**
     * Split user name into namespace and class name.
     *
     * @return array [namespace, name]
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

    private function baseNamespace(string $element): string
    {
        if (\array_key_exists('baseNamespace', $this->config['declarations'][$element])) {
            return \trim((string) $this->getOption($element, 'baseNamespace', ''), '\\');
        }

        return \trim($this->config['namespace'], '\\');
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
                $joinedPath = \rtrim($joinedPath, $joint) . $joint . \ltrim($chunk, $joint);
            }
        }

        return $joinedPath;
    }

    private function classify(string $name): string
    {
        return ( new InflectorFactory() )
            ->build()
            ->classify($name);
    }
}
