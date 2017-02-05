<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http\Fixtures;

use Spiral\Http\Request\RequestFilter;

class SimpleRequest extends RequestFilter
{
    const SCHEMA = [
        'name' => 'data:name'
    ];
}