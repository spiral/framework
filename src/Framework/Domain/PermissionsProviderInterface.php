<?php

declare(strict_types=1);

namespace Spiral\Domain;

interface PermissionsProviderInterface
{
    public function getPermissions(string $controller, string $action): ?array;
}
