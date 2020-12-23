<?php

declare(strict_types=1);

namespace Spiral\Domain;

interface PermissionsProviderInterface
{
    public function getPermission(string $controller, string $action): Permission;
}
