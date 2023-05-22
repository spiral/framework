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

        $this->assertSame('en', $d['value']);

        $this->assertTrue(in_array('en', $d['variants']));
        $this->assertTrue(in_array('ru', $d['variants']));
    }
}
