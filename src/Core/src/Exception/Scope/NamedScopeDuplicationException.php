<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Scope;

/**
 * @method string getScope()
 */
final class NamedScopeDuplicationException extends ScopeException
{
    public function __construct(
        string $scope,
    ) {
        parent::__construct(
            $scope,
            "Error on a scope allocation with the name `{$scope}`. A scope with the same name already exists.",
        );
    }
}
