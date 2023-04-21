<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Http\Exception;
use Spiral\Http\Exception\ClientException;
use Spiral\Http\Exception\ClientException\BadRequestException;
use Spiral\Http\Exception\ClientException\ForbiddenException;
use Spiral\Http\Exception\ClientException\NotFoundException;
use Spiral\Http\Exception\ClientException\ServerErrorException;
use Spiral\Http\Exception\ClientException\UnauthorizedException;

class ExceptionsTest extends TestCase
{
    public function testClientException(): void
    {
        $e = new ClientException();
        $this->assertSame(400, $e->getCode());
    }

    public function testNotFound(): void
    {
        $e = new NotFoundException();
        $this->assertSame(404, $e->getCode());
    }

    public function testBadRequest(): void
    {
        $e = new BadRequestException();
        $this->assertSame(400, $e->getCode());
    }

    public function testForbidden(): void
    {
        $e = new ForbiddenException();
        $this->assertSame(403, $e->getCode());
    }

    public function testUnauthorized(): void
    {
        $e = new UnauthorizedException();
        $this->assertSame(401, $e->getCode());
    }

    public function testServerError(): void
    {
        $e = new ServerErrorException();
        $this->assertSame(500, $e->getCode());
    }

    #[DataProvider('allExceptionsWithPreviousSet')]
    public function testPreviousSetter(\Throwable $exception): void
    {
        $this->assertInstanceOf(\Throwable::class, $exception->getPrevious());
    }

    public static function allExceptionsWithPreviousSet(): \Generator
    {
        yield [new Exception\ClientException\BadRequestException('', new \Exception())];
        yield [new Exception\ClientException\ForbiddenException('', new \Exception())];
        yield [new Exception\ClientException\NotFoundException('', new \Exception())];
        yield [new Exception\ClientException\ServerErrorException('', new \Exception())];
        yield [new Exception\ClientException\UnauthorizedException('', new \Exception())];
        yield [new Exception\ClientException(0, '', new \Exception())];
        yield [new Exception\DotNotFoundException('', 0, new \Exception())];
        yield [new Exception\HttpException('', 0, new \Exception())];
        yield [new Exception\InputException('', 0, new \Exception())];
        yield [new Exception\PipelineException('', 0, new \Exception())];
        yield [new Exception\ResponseException('', 0, new \Exception())];
    }
}
