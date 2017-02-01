<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http\Fixtures;

use Spiral\Http\Request\RequestFilter;

class BadPathRequest extends RequestFilter
{
    const SCHEMA = [
        'upload'      => 'file:.'
    ];

    const VALIDATES = [
        'upload'      => ['file:uploaded'],
        'description' => ['notEmpty']
    ];
}