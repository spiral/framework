<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http\RequestFilters;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Http\Request\InputInterface;
use Spiral\Tests\BaseTest;
use Spiral\Tests\Http\Fixtures\SimpleRequest;
use Zend\Diactoros\ServerRequest;

class ScopingTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Core\Exceptions\ScopeException
     */
    public function testInvalidScope()
    {
        $request = $this->container->get(SimpleRequest::class);
    }

    public function testInputScope()
    {
        $this->container->bind(InputInterface::class, new class implements InputInterface
        {
            public function getValue(string $source, string $name = null)
            {
                if ($source == 'data' && $name = 'name') {
                    return 'Antony';
                }
            }

            public function withPrefix(string $prefix): InputInterface
            {
                return $this;
            }
        });

        /**
         * @var SimpleRequest $request
         */
        $request = $this->container->get(SimpleRequest::class);

        $this->assertTrue($request->isValid());
        $this->assertSame('Antony', $request->name);
    }

    public function testRequestScope()
    {
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withParsedBody(['name' => 'John']);

        $this->container->bind(ServerRequestInterface::class, $serverRequest);

        /**
         * @var SimpleRequest $request
         */
        $request = $this->container->get(SimpleRequest::class);

        $this->assertTrue($request->isValid());
        $this->assertSame('John', $request->name);
    }
}