<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform\Import;

use Spiral\Stempler\Transform\Import\TagHelper;
use Spiral\Tests\Stempler\Transform\BaseTestCase;

final class TagHelperTest extends BaseTestCase
{
    public function testHasPrefixWithNullPrefix(): void
    {
        self::assertTrue(TagHelper::hasPrefix('foo:bar', null));
        self::assertTrue(TagHelper::hasPrefix('bar/foo', null));
    }

    public function testHasPrefixWithValidPrefix(): void
    {
        self::assertTrue(TagHelper::hasPrefix('foo:bar', 'foo'));
        self::assertTrue(TagHelper::hasPrefix('foo.bar', 'foo'));
        self::assertTrue(TagHelper::hasPrefix('foo/bar', 'foo'));
    }

    public function testHasPrefixWithInvalidPrefix(): void
    {
        self::assertFalse(TagHelper::hasPrefix('bar:foo', 'foo'));
        self::assertFalse(TagHelper::hasPrefix('foobar', 'foo'));
        self::assertFalse(TagHelper::hasPrefix('foo-bar', 'foo'));
    }

    public function testHasPrefixEdgeCases(): void
    {
        self::assertFalse(TagHelper::hasPrefix('foo', 'foo'));
        self::assertFalse(TagHelper::hasPrefix('foo:', 'foo'));
    }

    public function testStripPrefixWithValidPrefix(): void
    {
        self::assertSame('bar', TagHelper::stripPrefix('foo:bar', 'foo'));
        self::assertSame('bar', TagHelper::stripPrefix('foo.bar', 'foo'));
        self::assertSame('bar', TagHelper::stripPrefix('foo/bar', 'foo'));
    }

    public function testStripPrefixWithInvalidPrefix(): void
    {
        self::assertSame('bar:foo', TagHelper::stripPrefix('bar:foo', 'foo'));
        self::assertSame('foobar', TagHelper::stripPrefix('foobar', 'foo'));
        self::assertSame('foo-bar', TagHelper::stripPrefix('foo-bar', 'foo'));
    }

    public function testStripPrefixEdgeCases(): void
    {
        self::assertSame('foo', TagHelper::stripPrefix('foo', 'foo'));
        self::assertSame('foo:', TagHelper::stripPrefix('foo:', 'foo'));
    }
}
