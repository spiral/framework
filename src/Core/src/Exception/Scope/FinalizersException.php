<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Scope;

use Exception;
use Spiral\Core\Exception\Container\ContainerException;
use Throwable;

class FinalizersException extends ContainerException
{
    /**
     * @param Throwable[] $exceptions
     */
    public function __construct(
        protected ?string $scope,
        protected array $exceptions,
    ) {
        $count = \count($exceptions);
        parent::__construct(
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

    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @return Throwable[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }
}
