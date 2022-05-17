<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Spiral\Tests\Framework\HttpTest;

class FilterTest extends HttpTest
{
    public function testValid(): void
    {
        $this->assertSame('{"name":"hello","sectionValue":null}', (string)$this->post('/filter', [
            'name' => 'hello'
        ])->getBody());
    }

    public function testDotNotation(): void
    {
        $this->assertSame('{"name":"hello","sectionValue":"abc"}', (string)$this->post('/filter', [
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
