<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Filters;

use Spiral\Filters\ArrayInput;
use Spiral\Tests\Filters\Fixtures\MessageFilter;

class MessageFilterTest extends BaseTest
{
    public function testValid(): void
    {
        $filter = $this->getProvider()->createFilter(MessageFilter::class, new ArrayInput([
            'id' => 'value'
        ]));

        $this->assertTrue($filter->isValid());
    }

    public function testInvalid(): void
    {
        $filter = $this->getProvider()->createFilter(MessageFilter::class, new ArrayInput([
            'key' => 'value'
        ]));

        $this->assertFalse($filter->isValid());

        $this->assertSame([
            'id' => 'ID is not valid.'
        ], $filter->getErrors());
    }
}
