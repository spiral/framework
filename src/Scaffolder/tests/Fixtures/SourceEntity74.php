<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Fixtures;

class SourceEntity74
{
    public $noTypeString;

    /** @var SourceEntity74 */
    public $obj;

    protected bool $typedBool;

    /** @var int */
    protected $intFromPhpDoc;

    private $noTypeWithFloatDefault = 1.1;
}
