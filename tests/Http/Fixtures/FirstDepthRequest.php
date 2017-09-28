<?php

namespace Spiral\Tests\Http\Fixtures;

use Spiral\Http\Request\RequestFilter;

class FirstDepthRequest extends RequestFilter
{
    const SCHEMA = [
        'first' => SecondDepthRequest::class
    ];
}