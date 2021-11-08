<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Filter\InputScope;
use Spiral\Filters\InputInterface;

final class InputScopeTest extends BaseTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->container->bindSingleton(InputInterface::class, InputScope::class);
        $this->container->bindSingleton(
            ServerRequestInterface::class,
            (new ServerRequest('POST', '/test'))->withParsedBody([
                'foo' => 'value',
                'bar' => [
                    'empty' => null,
                ],
            ])
        );
    }

    public function testGetValue(): void
    {
        $input = $this->getInput();
        $this->assertSame('value', $input->getValue('data', 'foo'));
        $this->assertNull($input->getValue('data', 'bar.empty'));
    }

    public function testGetValueWithNonExistingKey(): void
    {
        $input = $this->getInput();
        $this->assertNull($input->getValue('data', 'invalid_key'));
    }

    public function testHasValue(): void
    {
        $input = $this->getInput();
        $this->assertFalse($input->hasValue('data', 'invalid_key'));
        $this->assertTrue($input->hasValue('data', 'foo'));
        $this->assertTrue($input->hasValue('data', 'bar'));
    }

    public function testHasValueNested(): void
    {
        $input = $this->getInput();

        $this->assertTrue($input->hasValue('data', 'bar.empty'));
        $this->assertFalse($input->hasValue('data', 'bar.empty.invalid_key'));
    }

    public function testHasValueWithNonExistingSource(): void
    {
        $input = $this->getInput();
        $this->assertFalse($input->hasValue('query', 'foo'));
    }

    private function getInput(): InputInterface
    {
        return $this->container->get(InputInterface::class);
    }
}
