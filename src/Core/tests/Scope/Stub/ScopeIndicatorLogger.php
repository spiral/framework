<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope\Stub;

use Psr\Container\ContainerInterface;
use Spiral\Core\Internal\Introspector;

/**
 * Returns the scope name where it was created
 */
final class ScopeIndicatorLogger implements LoggerInterface
{
    private ?string $scope;

    public function __construct(ContainerInterface $container)
    {
        $this->scope = Introspector::scopeName($container);
    }

    public function getName(): string
    {
        return $this->scope ?? '-NULL-';
    }
}
