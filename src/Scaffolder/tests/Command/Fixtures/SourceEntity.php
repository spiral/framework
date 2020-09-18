<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @author Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command\Fixtures;

class SourceEntity
{
    public $noTypeString;

    /** @var SourceEntity */
    public $obj;

    /** @var int */
    protected $intFromPhpDoc;

    private $noTypeWithFloatDefault = 1.1;
}
