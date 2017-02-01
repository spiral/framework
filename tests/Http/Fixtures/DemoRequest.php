<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http\Fixtures;

use Spiral\Http\Request\RequestFilter;

class DemoRequest extends RequestFilter
{
    const SCHEMA = [
        'name'    => 'data',
        'address' => AddressRequest::class,
        'uploads' => [UploadRequest::class, 'files.*', 'data:files'] //Iterate keys over data:files
    ];

    const VALIDATES = [
        'name'    => ['notEmpty'],
        'uploads' => [
            ['notEmpty', 'message' => '[[Please upload at least one file]]']
        ]
    ];

    const SETTERS = [
        'name' => 'strval'
    ];
}