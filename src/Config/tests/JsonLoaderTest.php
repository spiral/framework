<?php

declare(strict_types=1);

namespace Spiral\Tests\Config;

use Spiral\Config\Exception\LoaderException;

class JsonLoaderTest extends BaseTest
{
    public function testGetConfig(): void
    {
        $cf = $this->getFactory();

        $this->assertEquals(['name' => 'value'], $cf->getConfig('json'));
    }

    public function testEmpty(): void
    {
        $this->expectException(LoaderException::class);

        $cf = $this->getFactory();
        $cf->getConfig('empty-json');
    }

    public function testBroken(): void
    {
        $this->expectException(LoaderException::class);

        $cf = $this->getFactory();
        $cf->getConfig('broken-json');
    }
}
