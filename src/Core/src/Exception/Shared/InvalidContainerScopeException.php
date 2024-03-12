<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Shared;

use Psr\Container\NotFoundExceptionInterface;
use Spiral\Core\Container;
use Spiral\Core\Internal\Introspector;

/**
 * The exception may be thrown when the Container is unable to resolve requested dependency in the given scope.
 */
class InvalidContainerScopeException extends \RuntimeException implements NotFoundExceptionInterface
{
    protected string $scope;
    public function __construct(
        protected readonly string $id,
        Container|string|null $scopeOrContainer = null,
        protected readonly ?string $requiredScope = null,
    ) {
        $this->scope = \is_string($scopeOrContainer)
            ? $scopeOrContainer
            : Introspector::scopeName($scopeOrContainer);

        $req = $this->requiredScope !== null ? ", `$this->requiredScope` is required" : '';

        parent::__construct("Unable to resolve `$id` in the `$this->scope` scope{$req}.");
    }
}
