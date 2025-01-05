<?php

declare(strict_types=1);

namespace Spiral\Tests\Config;

use Spiral\Config\Exception\LoaderException;
use Spiral\Core\Container\Autowire;

class ConfigFactoryTest extends BaseTestCase
{
    public function testGetConfig(): void
    {
        $cf = $this->getFactory();
        $config = $cf->getConfig('test');

        self::assertEquals([
            'id'       => 'hello world',
            'autowire' => new Autowire('something'),
        ], $config);

        self::assertSame($config, $cf->getConfig('test'));
    }

    public function testExists(): void
    {
        $cf = $this->getFactory();
        self::assertTrue($cf->exists('test'));
        self::assertFalse($cf->exists('magic'));

        $cf->setDefaults('magic', ['key' => 'value']);

        self::assertTrue($cf->exists('magic'));
    }

    public function testConfigError(): void
    {
        $this->expectException(LoaderException::class);

        $cf = $this->getFactory();
        $cf->getConfig('other');
    }
}
