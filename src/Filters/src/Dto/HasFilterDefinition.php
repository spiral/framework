<?php

declare(strict_types=1);

namespace Spiral\Filters\Dto;

interface HasFilterDefinition
{
    public function filterDefinition(): FilterDefinitionInterface;
}
