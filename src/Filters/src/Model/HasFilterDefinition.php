<?php

declare(strict_types=1);

namespace Spiral\Filters\Model;

interface HasFilterDefinition
{
    public function filterDefinition(): FilterDefinitionInterface;
}
