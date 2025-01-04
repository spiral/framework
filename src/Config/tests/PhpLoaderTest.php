<?php

declare(strict_types=1);

namespace Spiral\Tests\Config;

use Spiral\Config\Exception\LoaderException;
use Spiral\Core\Container\Autowire;

class PhpLoaderTest extends BaseTestCase
{
    public function testGetConfig(): void
    {
        $cf = $this->getFactory();

        self::assertEquals([
            'id'       => 'hello world',
            'autowire' => new Autowire('something'),
        ], $cf->getConfig('test'));
    }

    public function testEmpty(): void
    {
        $this->expectException(LoaderException::class);

        $cf = $this->getFactory();
        $cf->getConfig('empty');
    }

    public function testBroken(): void
    {
        $this->expectException(LoaderException::class);

        $cf = $this->getFactory();
        $cf->getConfig('broken');
    }

    public function testScope(): void
    {
        $cf = $this->getFactory();
        $config = $cf->getConfig('scope');
        self::assertSame(['value' => 'value!'], $config);

        $this->container->bind(Value::class, new Value('other!'));

        $config = $cf->getConfig('scope2');
        self::assertSame(['value' => 'other!'], $config);

        $cf = clone $cf;

        $config = $cf->getConfig('scope');
        self::assertSame(['value' => 'other!'], $config);
    }
}
