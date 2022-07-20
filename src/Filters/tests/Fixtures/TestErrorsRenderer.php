<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Fixtures;

use Spiral\Filters\FilterInterface;
use Spiral\Filters\RenderErrors;

/**
 * @template-implements RenderErrors<FilterInterface>
 */
final class TestErrorsRenderer implements RenderErrors
{
    public function render(FilterInterface $filter)
    {
        return [
            'success' => false,
            'errors' => $filter->getErrors(),
        ];
    }
}
