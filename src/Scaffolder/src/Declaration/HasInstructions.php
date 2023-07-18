<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

interface HasInstructions
{
    /**
     * @return array<int, non-empty-string>
     */
    public function getInstructions(): array;
}
