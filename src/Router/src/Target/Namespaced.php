<?php

declare(strict_types=1);

namespace Spiral\Router\Target;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use Spiral\Router\Exception\TargetException;
use Spiral\Router\UriHandler;

/**
 * Provides ability to invoke any controller from given namespace.
 *
 * Example: new Namespaced("App\Controllers");
 *
 * @psalm-import-type Matches from UriHandler
 */
final class Namespaced extends AbstractTarget
{
    private readonly string $namespace;
    private readonly string $postfix;
    private readonly Inflector $inflector;

    public function __construct(
        string $namespace,
        string $postfix = 'Controller',
        int $options = 0
    ) {
        $this->namespace = \rtrim($namespace, '\\');
        $this->postfix = \ucfirst($postfix);

        parent::__construct(
            ['controller' => null, 'action' => null],
            ['controller' => null, 'action' => null],
            $options
        );

        $this->inflector = (new InflectorFactory())->build();
    }

    /**
     * @param Matches $matches
     */
    protected function resolveController(array $matches): string
    {
        if (\preg_match('/[^a-z_0-9\-]/i', $matches['controller'])) {
            throw new TargetException('Invalid namespace target, controller name not allowed.');
        }

        return \sprintf(
            '%s\\%s%s',
            $this->namespace,
            $this->inflector->classify($matches['controller']),
            $this->postfix
        );
    }

    /**
     * @param Matches $matches
     */
    protected function resolveAction(array $matches): ?string
    {
        return $matches['action'];
    }
}
