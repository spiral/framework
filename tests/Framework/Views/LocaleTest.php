<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Views;

use Spiral\Tests\Framework\BaseTestCase;
use Spiral\Translator\Views\LocaleDependency;

final class LocaleTest extends BaseTestCase
{
    public function testRenderEn(): void
    {
        $this->assertViewSame('custom:locale', expected: 'Hello English!');
    }

    public function testRenderRu(): void
    {
        $this->withLocale('ru')
            ->assertViewSame('custom:locale', expected: 'Hello Мир!');
    }

    public function testLocaleDependency(): void
    {
        $d = $this->getContainer()->get(LocaleDependency::class);

        $d = $d->__debugInfo();

        self::assertSame('en', $d['value']);

        self::assertContains('en', $d['variants']);
        self::assertContains('ru', $d['variants']);
    }
}
