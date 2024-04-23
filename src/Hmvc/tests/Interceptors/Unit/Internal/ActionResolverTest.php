<?php

declare(strict_types=1);

namespace Spiral\Tests\Interceptors\Unit\Internal;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Spiral\Interceptors\Exception\TargetCallException;
use Spiral\Interceptors\Internal\ActionResolver;
use Spiral\Tests\Interceptors\Unit\Stub\TestService;

final class ActionResolverTest extends TestCase
{
    public function testValidateControllerStaticMethod(): void
    {
        self::expectException(TargetCallException::class);
        self::expectExceptionMessageMatches('/Invalid action/');

        /** @see TestService::toUpperCase */
        ActionResolver::validateControllerMethod(new \ReflectionMethod(TestService::class, 'toUpperCase'));
    }

    public function testValidateControllerNonPublicMethod(): void
    {
        self::expectException(TargetCallException::class);
        self::expectExceptionMessageMatches('/Invalid action/');

        /** @see TestService::toLowerCase */
        ActionResolver::validateControllerMethod(new \ReflectionMethod(TestService::class, 'toLowerCase'));
    }

    public function testValidateControllerWithWrongControllerObject(): void
    {
        self::expectException(TargetCallException::class);
        self::expectExceptionMessageMatches('/Invalid controller/');

        /** @see TestService::increment */
        ActionResolver::validateControllerMethod(
            new \ReflectionMethod(TestService::class, 'increment'),
            new \stdClass(),
        );
    }

    #[DoesNotPerformAssertions]
    public function testValidateControllerWithControllerObject(): void
    {
        /** @see TestService::increment */
        ActionResolver::validateControllerMethod(
            new \ReflectionMethod(TestService::class, 'increment'),
            new TestService(),
        );
    }

    #[DoesNotPerformAssertions]
    public function testValidateControllerWithoutControllerObject(): void
    {
        /** @see TestService::increment */
        ActionResolver::validateControllerMethod(new \ReflectionMethod(TestService::class, 'increment'));
    }
}
