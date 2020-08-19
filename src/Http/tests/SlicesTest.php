<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Container;
use Spiral\Http\Request\InputManager;
use Laminas\Diactoros\ServerRequest;

class SlicesTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var InputManager
     */
    private $input;

    public function setUp(): void
    {
        $this->container = new Container();
        $this->input = new InputManager($this->container);
    }

    public function testNoSlice(): void
    {
        $this->container->bind(ServerRequestInterface::class, (new ServerRequest())->withParsedBody([
            'array' => [
                'key' => 'value'
            ]
        ]));

        $this->assertSame([
            'array' => [
                'key' => 'value'
            ]
        ], $this->input->data->all());
    }

    public function testSlice(): void
    {
        $this->container->bind(ServerRequestInterface::class, (new ServerRequest())->withParsedBody([
            'array' => [
                'key' => 'value'
            ]
        ]));

        $this->assertSame([
            'key' => 'value'
        ], $this->input->withPrefix('array')->data->all());
    }

    public function testDeadEnd(): void
    {
        $this->container->bind(ServerRequestInterface::class, (new ServerRequest())->withParsedBody([
            'array' => [
                'key' => 'value'
            ]
        ]));

        $this->assertSame([], $this->input->withPrefix('other')->data->all());
    }

    public function testMultiple(): void
    {
        $this->container->bind(ServerRequestInterface::class, (new ServerRequest())->withParsedBody([
            'array' => [
                'key' => [
                    'name' => 'value'
                ]
            ]
        ]));

        $this->assertSame([
            'name' => 'value'
        ], $this->input->withPrefix('array.key')->data->all());

        $input = $this->input->withPrefix('array');

        $this->assertSame([
            'key' => [
                'name' => 'value'
            ]
        ], $input->data->all());

        $input = $input->withPrefix('key');

        $this->assertSame([
            'name' => 'value'
        ], $input->data->all());

        $input = $input->withPrefix('', false);

        $this->assertSame([
            'array' => [
                'key' => [
                    'name' => 'value'
                ]
            ]
        ], $input->data->all());

        $this->assertSame('value', $input->data('array.key.name'));
        $this->assertSame('value', $input->post('array.key.name'));
    }
}
