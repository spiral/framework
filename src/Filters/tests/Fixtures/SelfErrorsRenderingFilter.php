<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Fixtures;

use Spiral\Filters\Filter;
use Spiral\Filters\ShouldRenderErrors;

final class SelfErrorsRenderingFilter extends Filter implements ShouldRenderErrors
{
    public const SCHEMA = [
        'id' => 'query:id'
    ];

    public const VALIDATES = [
        'id' => [
            ['notEmpty', 'err' => '[[ID is not valid.]]']
        ]
    ];

    public function render()
    {
        return ['success' => false, 'errors' => $this->getErrors(), 'requestParams' => $this->getFields()];
    }
}
