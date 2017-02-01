<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http\Fixtures;

use Spiral\Http\Request\RequestFilter;

class UploadRequest extends RequestFilter
{
    const SCHEMA = [
        'upload'      => 'file:file',
        'description' => 'data:label'
    ];

    const VALIDATES = [
        'upload'      => ['file:uploaded'],
        'description' => ['notEmpty']
    ];
}