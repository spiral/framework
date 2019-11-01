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
    public function testNotEmpty(): void
    {
        // pre-validated
        $this->assertSame(
            '{"status":400,"errors":{"name":"This value is required."}}',
            (string)$this->get('/filter')->getBody()
        );
    }

    public function testValid(): void
    {
        $this->assertSame('ok', (string)$this->post('/filter', [
            'name' => 'hello'
        ])->getBody());
    }

    public function testDotNotation(): void
    {
        $this->assertSame('abc', (string)$this->post('/filter', [
            'name'    => 'hello',
            'section' => ['value' => 'abc']
        ])->getBody());
    }

    public function testBadRequest(): void
    {
        $this->assertSame(500, $this->get('/filter2')->getStatusCode());
    }

    public function testInputTest(): void
    {
        $this->assertSame('value: abc', (string)$this->get('/input', [
            'section' => ['value' => 'abc']
        ])->getBody());
    }
}
