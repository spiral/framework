<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http\RequestFilters;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Tests\Http\Fixtures\BadPathRequest;
use Spiral\Tests\Http\HttpTest;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\UploadedFile;

class BadConfigurationTest extends HttpTest
{
    /**
     * @expectedException \Spiral\Http\Exceptions\InputException
     * @expectedExceptionMessage Invalid input location with error 'File not received, please try
     *                           again.', make sure to use proper pattern 'data:field_name'
     */
    public function testBadRequest()
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

        /** @var BadPathRequest $request */
        $request = $this->container->get(BadPathRequest::class);

        $this->assertTrue($request->isValid());
    }
}