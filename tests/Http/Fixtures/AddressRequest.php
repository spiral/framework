<?php
/**
 * spiral-empty.dev
 *
 * @author    Wolfy-J
 */
namespace Spiral\Tests\Http\Fixtures;

use Spiral\Http\Request\RequestFilter;

class AddressRequest extends RequestFilter
{
    const SCHEMA = [
        'country' => 'data:countryCode',
        'city'    => 'data',
        'address' => 'data',
    ];

    const VALIDATES = [
        'country' => ['notEmpty'],
        'city'    => ['notEmpty'],
        'address' => ['notEmpty'],
    ];

}