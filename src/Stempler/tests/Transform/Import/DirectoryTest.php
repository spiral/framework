<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform\Import;

use Mockery as m;
use Spiral\Stempler\Builder;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Loader\Source;
use Spiral\Stempler\Transform\Import\Directory;
use Spiral\Tests\Stempler\Transform\BaseTestCase;

final class DirectoryTest extends BaseTestCase
{
    public function testResolveTagWithoutNamespace(): void
    {
        $directory = new Directory('path/to/dir', 'test');

        $loader = m::mock(LoaderInterface::class);
        $this->assertNull(
            $directory->resolve(new Builder($loader), 'span'),
        );
    }

    public function testResolveTagWithNamespace(): void
    {
        $directory = new Directory('path/to/dir', 'test');

        $loader = m::mock(LoaderInterface::class);

        $loader
            ->shouldReceive('load')
            ->once()
            ->with($path = 'path/to/dir/span')
            ->andReturn(new Source('<span></span>'));

        $template = $directory->resolve(new Builder($loader), 'test:span');

        $this->assertSame($path, $template->getContext()->getPath());
    }
}
