<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Config\Loader;

use Psr\Container\ContainerInterface;
use Spiral\Config\Exception\LoaderException;
use Spiral\Core\ContainerScope;

/**
 * Loads PHP files inside container scope.
 */
final class PhpLoader implements FileLoaderInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function loadFile(string $section, string $filename): array
    {
        try {
            return ContainerScope::runScope($this->container, function () use ($filename) {
                return (require $filename);
            });
        } catch (\Throwable $e) {
            throw new LoaderException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
