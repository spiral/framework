<?php

namespace Spiral\Tests\Http\Fixtures;

use Spiral\Http\Request\RequestFilter;

class ThirdDepthRequest extends RequestFilter
{
    const SCHEMA = [
        'third' => 'data:third'
    ];

    const VALIDATES = [
        'third' => ['notEmpty']
    ];
}