<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model;

use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\UuidInterface;
use Spiral\Attributes\Factory;
use Spiral\Attributes\ReaderInterface;
use Spiral\Filter\InputScope;
use Spiral\Filters\Model\FilterProvider;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Filters\Model\Interceptor\Core;
use Spiral\Filters\Model\Mapper\EnumCaster;
use Spiral\Filters\Model\Mapper\CasterRegistry;
use Spiral\Filters\Model\Mapper\CasterRegistryInterface;
use Spiral\Filters\Model\Mapper\UuidCaster;
use Spiral\Http\Request\InputManager;
use Spiral\Tests\Filters\BaseTestCase;
use Spiral\Tests\Filters\Fixtures\LogoutFilter;
use Spiral\Tests\Filters\Fixtures\SomeFilter;
use Spiral\Tests\Filters\Fixtures\Status;
use Spiral\Tests\Filters\Fixtures\UserFilter;

final class FilterProviderTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->container->bindSingleton(ReaderInterface::class, (new Factory())->create());
        $this->container->bindSingleton(
            CasterRegistryInterface::class,
            static fn (): CasterRegistry => new CasterRegistry([new EnumCaster(), new UuidCaster()])
        );
    }

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

        $provider = $this->container->make(FilterProvider::class, [
            'core' => new Core(),
        ]);
        $this->container->bind(FilterProviderInterface::class, $provider);

        /** @var SomeFilter $filter */
        $filter = $provider->createFilter(SomeFilter::class, $input);

        self::assertSame('John', $filter->name);
        self::assertSame('Some street', $filter->address->address);
        self::assertSame('Portland', $filter->address->city);
    }

    public function testCreateFilterWithInputAttribute(): void
    {
        $request = new ServerRequest('GET', '/');
        $request = $request->withQueryParams([
            'token' => 'some'
        ]);
        $this->container->bind(ServerRequestInterface::class, $request);

        $inputManager = new InputManager($this->container);
        $input = new InputScope($inputManager);

        $provider = $this->container->make(FilterProvider::class, [
            'core' => new Core(),
        ]);
        $this->container->bind(FilterProviderInterface::class, $provider);

        /** @var LogoutFilter $filter */
        $filter = $provider->createFilter(LogoutFilter::class, $input);

        self::assertSame('some', $filter->token);
    }

    public function testCreateFilterWithEnumAndUuid(): void
    {
        $request = new ServerRequest('POST', '/');
        $request = $request->withParsedBody([
            'name' => 'John',
            'status' => 'active',
            'activationStatus' => 'inactive',
            'groupUuid' => 'f0a0b2c0-5b4b-4a5c-8d3e-6f7a8f9b0c1d',
            'friendUuid' => 'f0a0b2c0-5b4b-4a5c-8d3e-6f7a8f9b0c2d',
        ]);
        $this->container->bind(ServerRequestInterface::class, $request);

        $inputManager = new InputManager($this->container);
        $input = new InputScope($inputManager);

        $provider = $this->container->make(FilterProvider::class, [
            'core' => new Core(),
        ]);
        $this->container->bind(FilterProviderInterface::class, $provider);

        /** @var UserFilter $filter */
        $filter = $provider->createFilter(UserFilter::class, $input);

        self::assertSame('John', $filter->name);
        self::assertInstanceOf(Status::class, $filter->status);
        self::assertSame(Status::Active, $filter->status);
        self::assertInstanceOf(Status::class, $filter->activationStatus);
        self::assertSame(Status::Inactive, $filter->activationStatus);
        self::assertInstanceOf(UuidInterface::class, $filter->groupUuid);
        self::assertSame('f0a0b2c0-5b4b-4a5c-8d3e-6f7a8f9b0c1d', $filter->groupUuid->toString());
        self::assertInstanceOf(UuidInterface::class, $filter->friendUuid);
        self::assertSame('f0a0b2c0-5b4b-4a5c-8d3e-6f7a8f9b0c2d', $filter->friendUuid->toString());
    }
}
