<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Scope;

/**
 * @method string getScope()
 */
final class BadScopeException extends ScopeException
{
    public function __construct(
        string $scope,
        protected string $className,
    ) {
        parent::__construct(
            $scope,
            \sprintf('Class `%s` can be resolved only in `%s` scope.', $className, $scope),
        );
    }
}
