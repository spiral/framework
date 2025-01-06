<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform\Import;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Stempler\Builder;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Loader\Source;
use Spiral\Stempler\Transform\Import\Directory;
use Spiral\Tests\Stempler\Transform\BaseTestCase;

final class DirectoryTest extends BaseTestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public static function wrongNamespaceProvider(): iterable
    {
        yield ['span'];
        yield ['abcd:span'];
        yield ['test1:span'];
        yield ['abc:span'];
        yield ['tes:span'];
    }

    #[DataProvider('wrongNamespaceProvider')]
    public function testResolveTagWithWrongNamespace(string $tag): void
    {
        $directory = new Directory('path/to/dir', 'test');

        $loader = m::mock(LoaderInterface::class);
        self::assertNull(
            $directory->resolve(new Builder($loader), $tag),
        );
    }

    public static function correctNamespaceProvider(): iterable
    {
        yield ['test.span'];
        yield ['test:span'];
        yield ['test/span'];
    }

    #[DataProvider('correctNamespaceProvider')]
    public function testResolveTagWithCorrectNamespace(string $tag): void
    {
        $directory = new Directory('path/to/dir', 'test');

        $loader = m::mock(LoaderInterface::class);

        $loader
            ->shouldReceive('load')
            ->once()
            ->with($path = 'path/to/dir/span')
            ->andReturn(new Source('<span></span>'));

        $template = $directory->resolve(new Builder($loader), $tag);

        self::assertSame($path, $template->getContext()->getPath());
    }
}
