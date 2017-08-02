<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http\RequestFilters;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Tests\Http\Fixtures\DemoRequest;
use Spiral\Tests\Http\HttpTest;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\UploadedFile;

class DemoRequestTest extends HttpTest
{
    public function testInitiation()
    {
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody([]);
        $this->container->bind(ServerRequestInterface::class, $serverRequest);

        /** @var DemoRequest $request */
        $request = $this->container->get(DemoRequest::class);

        $this->assertFalse($request->isValid());

        $this->assertArrayHasKey('name', $request->getErrors());
        $this->assertArrayHasKey('files', $request->getErrors());
        $this->assertArrayHasKey('name', $request->getErrors());
        $this->assertArrayHasKey('address', $request->getErrors());
        $this->assertArrayHasKey('countryCode', $request->getErrors()['address']);
        $this->assertArrayHasKey('city', $request->getErrors()['address']);
        $this->assertArrayHasKey('address', $request->getErrors()['address']);
    }

    public function testPartiallyValidOverIterated()
    {
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody([
            'name'    => 'Anton',
            'address' => [
                'countryCode' => '',
                'city'        => 'San Francisco',
                'address'     => 'Some street'
            ],
            'files'   => [
                //Iterating over data
                0 => [
                    'label' => 'Some label'
                ]
            ]
        ]);
        $this->container->bind(ServerRequestInterface::class, $serverRequest);

        /** @var DemoRequest $request */
        $request = $this->container->get(DemoRequest::class);

        $this->assertFalse($request->isValid());

        $this->assertArrayNotHasKey('name', $request->getErrors());
        $this->assertArrayHasKey('files', $request->getErrors());
        $this->assertArrayHasKey('address', $request->getErrors());
        $this->assertArrayHasKey('countryCode', $request->getErrors()['address']);
        $this->assertArrayNotHasKey('city', $request->getErrors()['address']);
        $this->assertArrayNotHasKey('address', $request->getErrors()['address']);

        $this->assertArrayHasKey('file', $request->getErrors()['files'][0]);
        $this->assertArrayNotHasKey('label', $request->getErrors()['files'][0]);
    }

    public function testFilesAndExtraFiles()
    {
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody([
            'name'    => 'Anton',
            'address' => [
                'countryCode' => '',
                'city'        => 'San Francisco',
                'address'     => 'Some street'
            ],
            'files'   => [
                //Iterating over data
                0 => [
                    'label' => 'Some label'
                ]
            ]
        ]);

        //We have to use same namespace as data
        $serverRequest = $serverRequest->withUploadedFiles([
            'files' => [
                0 => [
                    'file' => new UploadedFile(fopen(__FILE__, 'r'), filesize(__FILE__), 0)
                ],
                1 => [
                    'file' => new UploadedFile(fopen(__FILE__, 'r'), filesize(__FILE__), 1)
                ],
            ]
        ]);

        $this->container->bind(ServerRequestInterface::class, $serverRequest);

        /** @var DemoRequest $request */
        $request = $this->container->get(DemoRequest::class);

        $this->assertFalse($request->isValid());

        $this->assertArrayNotHasKey('name', $request->getErrors());
        $this->assertArrayNotHasKey('files', $request->getErrors());
        $this->assertArrayHasKey('address', $request->getErrors());
        $this->assertArrayHasKey('countryCode', $request->getErrors()['address']);
        $this->assertArrayNotHasKey('city', $request->getErrors()['address']);
        $this->assertArrayNotHasKey('address', $request->getErrors()['address']);
    }

    public function testFilesAndExtraFilesInvalidFile()
    {
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody([
            'name'    => 'Anton',
            'address' => [
                'countryCode' => '',
                'city'        => 'San Francisco',
                'address'     => 'Some street'
            ],
            'files'   => [
                //Iterating over data
                0 => [
                    'label' => 'Some label'
                ]
            ]
        ]);

        $serverRequest = $serverRequest->withUploadedFiles([
            'files' => [
                0 => [
                    'file' => new UploadedFile(fopen(__FILE__, 'r'), filesize(__FILE__), 2)
                ],
                1 => [
                    'file' => new UploadedFile(fopen(__FILE__, 'r'), filesize(__FILE__), 1)
                ],
            ]
        ]);

        $this->container->bind(ServerRequestInterface::class, $serverRequest);

        /** @var DemoRequest $request */
        $request = $this->container->get(DemoRequest::class);

        $this->assertFalse($request->isValid());

        $this->assertArrayNotHasKey('name', $request->getErrors());
        $this->assertArrayHasKey('files', $request->getErrors());
        $this->assertArrayHasKey('address', $request->getErrors());
        $this->assertArrayHasKey('countryCode', $request->getErrors()['address']);
        $this->assertArrayNotHasKey('city', $request->getErrors()['address']);
        $this->assertArrayNotHasKey('address', $request->getErrors()['address']);

        $this->assertArrayHasKey('file', $request->getErrors()['files'][0]);
        $this->assertArrayNotHasKey('label', $request->getErrors()['files'][0]);
    }

    public function testValid()
    {
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody([
            'name'    => 'Anton',
            'address' => [
                'countryCode' => 'US',
                'city'        => 'San Francisco',
                'address'     => 'Some street'
            ],
            'files'   => [
                //Iterating over data
                0 => [
                    'label' => 'Some label'
                ]
            ]
        ]);

        $serverRequest = $serverRequest->withUploadedFiles([
            'files' => [
                0 => [
                    'file' => new UploadedFile(fopen(__FILE__, 'r'), filesize(__FILE__), 0)
                ],
                1 => [
                    'file' => new UploadedFile(fopen(__FILE__, 'r'), filesize(__FILE__), 1)
                ],
            ]
        ]);

        $this->container->bind(ServerRequestInterface::class, $serverRequest);

        /** @var DemoRequest $request */
        $request = $this->container->get(DemoRequest::class);

        $this->assertTrue($request->isValid());
        $this->assertInternalType('array', $request->__debugInfo());

        $this->assertTrue($request->isValid(true));
        $name = $request['name'];
        unset($request['name']);
        $this->assertTrue($request->isValid(true));

        $request->name = $name;
        $this->assertTrue($request->isValid(true));
    }

    public function testContext()
    {
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody([
            'name'    => 'Anton',
            'address' => [
                'countryCode' => '',
                'city'        => 'San Francisco',
                'address'     => 'Some street'
            ],
            'files'   => [
                //Iterating over data
                0 => [
                    'label' => 'Some label'
                ]
            ]
        ]);
        $this->container->bind(ServerRequestInterface::class, $serverRequest);
        /** @var DemoRequest $request */
        $request = $this->container->get(DemoRequest::class);

        $context = new \stdClass();
        $context->data = 'some context';
        $request->setContext($context);

        $this->assertEquals($context, $request->getContext());
    }
}
