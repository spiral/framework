<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Scope;

use Exception;
use Throwable;

final class FinalizersException extends ScopeException
{
    /**
     * @param Throwable[] $exceptions
     */
    public function __construct(
        ?string $scope,
        protected array $exceptions,
    ) {
        $count = \count($exceptions);
        parent::__construct(
            $scope,
            \sprintf(
                "%s thrown during finalization of %s:\n%s",
                $count === 1 ? 'An exception has been' : "$count exceptions have been",
                $scope === null ? 'an unnamed scope' : "the scope `$scope`",
                \implode("\n\n", \array_map(
                    static fn (Exception $e): string => \sprintf(
                        "# %s\n%s",
                        $e::class,
                        $e->getMessage(),
                    ),
                    $exceptions,
                )),
            ),
        );
    }

    /**
     * @return Throwable[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }
}
