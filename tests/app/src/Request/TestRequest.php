<?php

declare(strict_types=1);

namespace Spiral\App\Request;

use Spiral\Filters\Attribute\Input\Data;
use Spiral\Filters\Attribute\Input\Method;
use Spiral\Filters\Model\Filter;

class TestRequest extends Filter
{
    #[Data(key: 'name')]
    public ?string $name = null;

    #[Data(key: 'section.value')]
    public ?string $sectionValue = null;

    #[Method]
    public string $method;

    // TODO: add tests for validation
    //    public const VALIDATES = [
    //        'name' => ['notEmpty', 'string']
    //    ];
}
