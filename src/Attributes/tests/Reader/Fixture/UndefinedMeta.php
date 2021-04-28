<?php

/**
 * This file is part of Attributes package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Reader\Fixture;

/** @HelloWorld */
#[HelloWorld]
class UndefinedMeta
{
    #[HelloWorld]
    public const CONSTANT = 0xDEADBEEF;

    /** @HelloWorld */
    #[HelloWorld]
    public $property = 0xDEADBEEF;

    /** @HelloWorld */
    #[HelloWorld]
    public function method(
        #[HelloWorld]
        int $parameter
    ): void {}
}

#[HelloWorld]
function undefined_meta()
{
}
