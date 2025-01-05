<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform\Import;

use Mockery as m;
use Spiral\Stempler\Builder;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Loader\Source;
use Spiral\Stempler\Transform\Import\Bundle;
use Spiral\Tests\Stempler\Transform\BaseTestCase;

final class BundleTest extends BaseTestCase
{
    public function testResolveTagWithoutNamespace(): void
    {
        $bundle = new Bundle('path/to/dir', 'test');
        $loader = m::mock(LoaderInterface::class);

        $loader
            ->shouldReceive('load')
            ->once()
            ->with($path = 'path/to/dir')
            ->andReturn(new Source('<span></span>'));

        $this->assertNull(
            $bundle->resolve(new Builder($loader), 'span'),
        );
    }
}
