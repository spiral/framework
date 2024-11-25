<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Fixtures;

class UserContextController
{
    public function __construct(
        private readonly UserContext $scope,
    ) {}

    public function scope(): string
    {
        return $this->scope ? 'OK' : 'FAIL';
    }
}
