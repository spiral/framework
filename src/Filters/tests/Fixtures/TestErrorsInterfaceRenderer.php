<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Fixtures;

use Spiral\Filters\FilterInterface;
use Spiral\Filters\RenderErrorsInterface;

/**
 * @template-implements RenderErrorsInterface<FilterInterface>
 */
final class TestErrorsInterfaceRenderer implements RenderErrorsInterface
{
    public function render(FilterInterface $filter)
    {
        return [
            'success' => false,
            'errors' => $filter->getErrors(),
        ];
    }
}
