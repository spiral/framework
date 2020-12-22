<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Validation\Checkers;

use Spiral\Tests\Validation\BaseTest;
use Spiral\Validation\Checker\ArrayChecker;

class ArrayTest extends BaseTest
{
    public function testOf(): void
    {
        /** @var ArrayChecker $checker */
        $checker = $this->container->get(ArrayChecker::class);

        $this->assertTrue($checker->of([1], 'is_int'));
        $this->assertTrue($checker->of([1], 'integer'));
        $this->assertTrue($checker->of(['1'], 'is_string'));

        $this->assertFalse($checker->of(1, 'is_int'));
        $this->assertFalse($checker->of([1], 'is_string'));
    }
}
