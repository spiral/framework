<?php

declare(strict_types=1);

namespace Spiral\Router\Loader;

use Spiral\Core\ResolverInterface;
use Spiral\Router\Exception\LoaderLoadException;

final class PhpFileLoader implements LoaderInterface
{
    public function __construct(
        private readonly ResolverInterface $resolver
    ) {
    }

    /**
     * Loads a PHP file.
     */
    public function load(mixed $resource, string $type = null): mixed
    {
        if (!\file_exists($resource)) {
            throw new LoaderLoadException('File [%s] does not exist.');
        }

        $load = static function (string $path) {
            return include $path;
        };

        $callback = $load($resource);

        $args = $this->resolver->resolveArguments(new \ReflectionFunction($callback), validate: true);

        return $callback(...$args);
    }

    public function supports(mixed $resource, string $type = null): bool
    {
        return
            \is_string($resource) &&
            \pathinfo($resource, \PATHINFO_EXTENSION) === 'php' &&
            (!$type || $type === 'php');
    }
}
