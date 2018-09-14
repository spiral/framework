<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Framework\Exceptions\DirectoryException;

class Directories implements DirectoriesInterface, SingletonInterface
{
    // todo: move to directory map
    public const DEFAULT_MAP = [
        'root'    => null,
        'public'  => null,
        'vendor'  => null,
        'app'     => null,
        'runtime' => null,
        'config'  => null,
        'cache'   => null
    ];

    /** @var array */
    private $directories = [];

    /**
     * @param array $directories
     */
    public function __construct(array $directories)
    {
        // todo: additional mapping
        $this->directories = $directories;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->directories);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $name, string $path): DirectoriesInterface
    {
        $this->directories[$name] = rtrim($path, '/\\') . '/';

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name): string
    {
        if (!$this->has($name)) {
            throw new DirectoryException("Undefined directory '{$name}'");
        }

        return $this->directories[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return $this->directories;
    }
}