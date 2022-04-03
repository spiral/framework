<?php

declare(strict_types=1);

namespace Spiral\Views;

use Spiral\Views\Exception\RenderException;

interface ViewInterface
{
    /**
     * Render view source using internal logic.
     *
     * @throws RenderException
     */
    public function render(array $data = []): string;
}
