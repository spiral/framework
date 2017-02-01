<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Psr\Http\Message\ResponseInterface;
use Spiral\Http\Response\ResponseWrapper;
use Spiral\Http\Uri;
use Zend\Diactoros\Response;
use function GuzzleHttp\json_encode;

class ResponseWrapperTest extends \PHPUnit_Framework_TestCase
{
    public function testWrapRedirect()
    {
        $response = new Response();
        $response = $response->withAddedHeader('Response', 'true');

        $wrapper = new ResponseWrapper($response);

        $this->assertInstanceOf(ResponseInterface::class, $response = $wrapper->redirect(
            '/'
        ));

        $this->assertSame('true', $response->getHeaderLine('Response'));
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/', $response->getHeaderLine('Location'));
    }

    public function testWrapRedirect301()
    {
        $response = new Response();
        $response = $response->withAddedHeader('Response', 'true');

        $wrapper = new ResponseWrapper($response);

        $this->assertInstanceOf(ResponseInterface::class, $response = $wrapper->redirect(
            '/',
            301
        ));

        $this->assertSame('true', $response->getHeaderLine('Response'));
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('/', $response->getHeaderLine('Location'));
    }

    public function testWrapRedirectUri()
    {
        $response = new Response();
        $response = $response->withAddedHeader('Response', 'true');

        $wrapper = new ResponseWrapper($response);

        $this->assertInstanceOf(ResponseInterface::class, $response = $wrapper->redirect(
            new Uri('http://google.com')
        ));

        $this->assertSame('true', $response->getHeaderLine('Response'));
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://google.com', $response->getHeaderLine('Location'));
    }

    public function testWrapJSON()
    {
        $response = new Response();
        $response = $response->withAddedHeader('Response', 'true');

        $wrapper = new ResponseWrapper($response);

        $this->assertInstanceOf(ResponseInterface::class, $response = $wrapper->json(
            ['hello' => 'world']
        ));

        $this->assertSame('true', $response->getHeaderLine('Response'));
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame(json_encode(['hello' => 'world']), $response->getBody()->__toString());
    }

    public function testAttachFile()
    {
        $response = new Response();
        $response = $response->withAddedHeader('Response', 'true');

        $wrapper = new ResponseWrapper($response);

        $this->assertInstanceOf(
            ResponseInterface::class,
            $response = $wrapper->attachment(__FILE__)
        );


        $this->assertSame(file_get_contents(__FILE__), $response->getBody()->__toString());
    }

    /**
     * @expectedException \Spiral\Http\Exceptions\ResponseException
     */
    public function testAttachStreamNoName()
    {
        $response = new Response();
        $response = $response->withAddedHeader('Response', 'true');

        $wrapper = new ResponseWrapper($response);

        $this->assertInstanceOf(
            ResponseInterface::class,
            $response = $wrapper->attachment(fopen(__FILE__, 'r'))
        );

        $this->assertSame(file_get_contents(__FILE__), $response->getBody()->__toString());
    }

    public function testAttachStream()
    {
        $response = new Response();
        $response = $response->withAddedHeader('Response', 'true');

        $wrapper = new ResponseWrapper($response);

        $this->assertInstanceOf(
            ResponseInterface::class,
            $response = $wrapper->attachment(fopen(__FILE__, 'r'), 'file.php')
        );

        $this->assertSame('attachment; filename="file.php"', $response->getHeaderLine('Content-Disposition'));
        $this->assertSame(file_get_contents(__FILE__), $response->getBody()->__toString());
    }
}