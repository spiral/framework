<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model;

use Mockery as m;
use Spiral\Filters\InputInterface;

abstract class AttributeTestCase extends m\Adapter\Phpunit\MockeryTestCase
{
    protected m\LegacyMockInterface|m\MockInterface|InputInterface $input;
    protected string $baz;

    protected function setUp(): void
    {
        parent::setUp();
        $this->input = m::mock(InputInterface::class);
    }

    protected function makeProperty(): \ReflectionProperty
    {
        return new \ReflectionProperty($this, 'baz');
    }
}
