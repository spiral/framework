<?php

declare(strict_types=1);

namespace Spiral\Tests\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\Exception\ConfiguratorException;

class DefaultsTest extends BaseTestCase
{
    public function testGetNonExistedByDefaultConfig(): void
    {
        $cf = $this->getFactory();
        $cf->setDefaults('magic', ['key' => 'value']);

        $config = $cf->getConfig('magic');

        self::assertSame(['key' => 'value'], $config);

        self::assertSame($config, $cf->getConfig('magic'));
    }

    public function testDefaultsTwice(): void
    {
        $this->expectException(ConfiguratorException::class);

        $cf = $this->getFactory();
        $cf->setDefaults('magic', ['key' => 'value']);
        $cf->setDefaults('magic', ['key' => 'value']);
    }

    public function testDefaultToAlreadyLoaded(): void
    {
        $this->expectException(ConfiguratorException::class);

        $cf = $this->getFactory();

        $cf->getConfig('test');
        $cf->setDefaults('test', ['key' => 'value']);
    }

    public function testOverwrite(): void
    {
        $cf = $this->getFactory();

        $cf->setDefaults('test', [
            'key' => 'value',
        ]);

        $config = $cf->getConfig('test');

        self::assertEquals([
            'key'      => 'value',
            'id'       => 'hello world',
            'autowire' => new Autowire('something'),
        ], $config);

        self::assertSame($config, $cf->getConfig('test'));
    }
}
