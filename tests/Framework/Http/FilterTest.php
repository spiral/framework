<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);


namespace Spiral\Framework\Http;

use Spiral\Framework\HttpTest;

class FilterTest extends HttpTest
{
    public function testNotEmpty()
    {
        $this->assertSame('{"name":"This value is required."}', (string)$this->get('/filter')->getBody());
    }

    public function testValid()
    {
        $this->assertSame('ok', (string)$this->post('/filter', [
            'name' => "hello"
        ])->getBody());
    }

    public function testDotNotation()
    {
        $this->assertSame('abc', (string)$this->post('/filter', [
            'name'    => "hello",
            'section' => ['value' => 'abc']
        ])->getBody());
    }

    public function testBadRequest()
    {
        $this->assertSame(500, $this->get('/filter2')->getStatusCode());
    }
}