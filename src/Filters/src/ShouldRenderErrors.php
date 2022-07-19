<?php

declare(strict_types=1);

namespace Spiral\Filters;

interface ShouldRenderErrors
{
    /**
     * @return mixed
     */
    public function render();
}
