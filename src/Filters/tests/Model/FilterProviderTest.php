<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model;

use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Attributes\Factory;
use Spiral\Filter\InputScope;
use Spiral\Filters\Model\FilterProvider;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Filters\Model\Interceptor\Core;
use Spiral\Filters\Model\Schema\AttributeMapper;
use Spiral\Http\Request\InputManager;
use Spiral\Tests\Filters\BaseTest;
use Spiral\Tests\Filters\Fixtures\SomeFilter;

final class FilterProviderTest extends BaseTest
{
    public function testCreateNestedFilterWithIdenticalPropertyNames(): void
    {
        $request = new ServerRequest('POST', '/');
        $request = $request->withParsedBody([
            'name' => 'John',
            'address' => [
                'address' => 'Some street',
                'city' => 'Portland'
            ]
        ]);
        $this->container->bind(ServerRequestInterface::class, $request);

        $inputManager = new InputManager($this->container);
        $input = new InputScope($inputManager);

        $provider = new FilterProvider($this->container, $this->container, new Core());
        $this->container->bind(FilterProviderInterface::class, $provider);

        $mapper = new AttributeMapper($provider, (new Factory())->create());
        $this->container->bind(AttributeMapper::class, $mapper);

        /** @var SomeFilter $filter */
        $filter = $provider->createFilter(SomeFilter::class, $input);

        $this->assertSame('John', $filter->name);
        $this->assertSame('Some street', $filter->address->address);
        $this->assertSame('Portland', $filter->address->city);
    }
}
