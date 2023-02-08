<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Scope;

final class ScopeContainerLeakedException extends ScopeException
{
    /**
     * @param array<int<0, max>, string|null> $parents
     */
    public function __construct(
        ?string $scope,
        array $parents,
    ) {
        $scopes = \implode('->', \array_map(
            static fn (?string $scope): string => $scope === null ? 'null' : "\"$scope\"",
            [...\array_reverse($parents), $scope],
        ));
        parent::__construct(
            $scope,
            \sprintf('Scoped container has been leaked. Scope: %s.', $scopes),
        );
    }
}
