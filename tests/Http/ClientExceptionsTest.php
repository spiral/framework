<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Spiral\Http\Exceptions\ClientExceptions\BadRequestException;
use Spiral\Http\Exceptions\ClientExceptions\ForbiddenException;
use Spiral\Http\Exceptions\ClientExceptions\NotFoundException;
use Spiral\Http\Exceptions\ClientExceptions\ServerErrorException;
use Spiral\Http\Exceptions\ClientExceptions\UnauthorizedException;

class ClientExceptionsTest extends HttpTest
{
    public function testNotFound()
    {
        $this->http->setEndpoint(function () {
            throw new NotFoundException();
        });

        $this->assertSame(404, $this->get('/')->getStatusCode());
    }

    public function testBadRequest()
    {
        $this->http->setEndpoint(function () {
            throw new BadRequestException();
        });

        $this->assertSame(400, $this->get('/')->getStatusCode());
    }

    public function testForbidden()
    {
        $this->http->setEndpoint(function () {
            throw new ForbiddenException();
        });

        $this->assertSame(403, $this->get('/')->getStatusCode());
    }

    public function testUnauthorized()
    {
        $this->http->setEndpoint(function () {
            throw new UnauthorizedException();
        });

        $this->assertSame(401, $this->get('/')->getStatusCode());
    }

    public function testServerError()
    {
        $this->http->setEndpoint(function () {
            throw new ServerErrorException();
        });

        $this->assertSame(500, $this->get('/')->getStatusCode());
    }

    /**
     * @expectedException \ErrorException
     */
    public function testException()
    {
        $this->http->setEndpoint(function () {
            throw new \ErrorException();
        });

        $this->assertSame(500, $this->get('/')->getStatusCode());
    }
}