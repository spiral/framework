<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Fixtures;

class SourceEntity74
{
    protected bool $typedBool;

    public $noTypeString;

    /** @var SourceEntity74 */
    public $obj;

    /** @var int */
    protected $intFromPhpDoc;

    private $noTypeWithFloatDefault = 1.1;
}
