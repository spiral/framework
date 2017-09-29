<?php

namespace Spiral\Tests\Http\Fixtures;

use Spiral\Http\Request\RequestFilter;

class SecondDepthRequest extends RequestFilter
{
    const SCHEMA = [
        'second' => ThirdDepthRequest::class
    ];
}