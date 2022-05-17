<?php

declare(strict_types=1);

namespace Spiral\Filters;

use Spiral\Auth\AuthContextInterface;

interface ShouldBeAuthorized
{
    public function isAuthorized(?AuthContextInterface $auth): bool;
}
