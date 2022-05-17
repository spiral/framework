<?php

declare(strict_types=1);

namespace Spiral\Filters;

interface HasFilterDefinition
{
    public function filterDefinition(): FilterDefinitionInterface;
}
