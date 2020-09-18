<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 * @author  Valentin V (vvval)
 */

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

    /** @var array */
    protected $config = [
        'header'       => [],
        'directory'    => '',
        'namespace'    => '',
        'declarations' => [],
    ];

    /**
     * @return array
     */
    public function headerLines(): array
    {
        return $this->config['header'];
    }

    /**
     * @return string
     */
    public function baseDirectory(): string
    {
        return $this->config['directory'];
    }

    /**
     * @param string $element
     * @param string $name
     * @return string
     */
    public function className(string $element, string $name): string
    {
        ['name' => $name] = $this->parseName($name);

        return $this->classify($name) . $this->elementPostfix($element);
    }

    /**
     * @param string $element
     * @param string $name
     * @return string
     */
    public function classNamespace(string $element, string $name = ''): string
    {
        $localNamespace = trim($this->getOption($element, 'namespace', ''), '\\');
        ['namespace' => $namespace] = $this->parseName($name);

        if (!empty($namespace)) {
            $localNamespace .= '\\' . $this->classify($namespace);
        }

        if (empty($this->baseNamespace())) {
            return $localNamespace;
        }

        return trim($this->baseNamespace() . '\\' . $localNamespace, '\\');
    }

    /**
     * @param string $element
     * @param string $name
     * @return string
     */
    public function classFilename(string $element, string $name): string
    {
        $namespace = $this->classNamespace($element, $name);
        $namespace = substr($namespace, strlen($this->baseNamespace()));

        return $this->joinPathChunks([
            $this->baseDirectory(),
            str_replace('\\', '/', $namespace),
            $this->className($element, $name) . '.php',
        ], '/');
    }

    /**
     * @param string $element
     * @return string
     * @throws ScaffolderException
     */
    public function declarationClass(string $element): string
    {
        $class = $this->getOption($element, 'class');

        if (empty($class)) {
            throw new ScaffolderException(
                "Unable to scaffold '{$element}', no declaration class found"
            );
        }

        return $class;
    }

    /**
     * Declaration options.
     *
     * @param string $element
     * @return array
     */
    public function declarationOptions(string $element): array
    {
        return $this->getOption($element, 'options', []);
    }

    /**
     * @param string $element
     * @return string
     */
    private function elementPostfix(string $element): string
    {
        return $this->getOption($element, 'postfix', '');
    }

    /**
     * @param string $element
     * @param string $section
     * @param mixed  $default
     * @return mixed
     */
    private function getOption(string $element, string $section, $default = null)
    {
        if (!isset($this->config['declarations'][$element])) {
            throw new ScaffolderException("Undefined declaration '{$element}'.");
        }

        if (array_key_exists($section, $this->config['declarations'][$element])) {
            return $this->config['declarations'][$element][$section];
        }

        return $default;
    }

    /**
     * Split user name into namespace and class name.
     *
     * @param string $name
     * @return array [namespace, name]
     */
    private function parseName(string $name): array
    {
        $name = str_replace('/', '\\', $name);

        if (strpos($name, '\\') !== false) {
            $names = explode('\\', $name);
            $class = array_pop($names);

            return ['namespace' => implode('\\', $names), 'name' => $class];
        }

        //No user namespace
        return ['namespace' => '', 'name' => $name];
    }

    /**
     * @return string
     */
    private function baseNamespace(): string
    {
        return trim($this->config['namespace'], '\\');
    }

    /**
     * @param array  $chunks
     * @param string $joint
     * @return string
     */
    private function joinPathChunks(array $chunks, string $joint): string
    {
        $firstChunkIterated = false;
        $joinedPath = '';
        foreach ($chunks as $chunk) {
            if (!$firstChunkIterated) {
                $firstChunkIterated = true;
                $joinedPath = $chunk;
            } else {
                $joinedPath = rtrim($joinedPath, $joint) . $joint . ltrim($chunk, $joint);
            }
        }

        return $joinedPath;
    }

    /**
     * @param string $name
     * @return string
     */
    private function classify(string $name): string
    {
        return ( new InflectorFactory() )
            ->build()
            ->classify($name);
    }
}
