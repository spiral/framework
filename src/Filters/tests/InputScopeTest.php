<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters;

use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Filter\InputScope;
use Spiral\Filters\InputInterface;
use Spiral\Http\Request\InputManager;

final class InputScopeTest extends BaseTestCase
{
    public function testGetValue(): void
    {
        $input = $this->getInput();
        self::assertSame('value', $input->getValue('data', 'foo'));
        self::assertNull($input->getValue('data', 'bar.empty'));
    }

    public function testGetValueWithNonExistingKey(): void
    {
        $input = $this->getInput();
        self::assertNull($input->getValue('data', 'invalid_key'));
    }

    public function testHasValue(): void
    {
        $input = $this->getInput();
        self::assertFalse($input->hasValue('data', 'invalid_key'));
        self::assertTrue($input->hasValue('data', 'foo'));
        self::assertTrue($input->hasValue('data', 'bar'));
    }

    public function testHasValueNested(): void
    {
        $input = $this->getInput();

        self::assertTrue($input->hasValue('data', 'bar.empty'));
        self::assertFalse($input->hasValue('data', 'bar.empty.invalid_key'));
    }

    public function testHasValueWithNonExistingSource(): void
    {
        $input = $this->getInput();
        self::assertFalse($input->hasValue('query', 'foo'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->container->bindSingleton(InputInterface::class, InputScope::class);
        $this->container->bindSingleton(InputManager::class, new InputManager($this->container));
        $this->container->bindSingleton(
            ServerRequestInterface::class,
            (new ServerRequest('POST', '/test'))->withParsedBody([
                'foo' => 'value',
                'bar' => [
                    'empty' => null,
                ],
            ]),
        );
    }

    private function getInput(): InputInterface
    {
        return $this->container->get(InputInterface::class);
    }
}
