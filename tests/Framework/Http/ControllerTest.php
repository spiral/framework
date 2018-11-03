<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Http;

use Spiral\Framework\HttpTest;

class ControllerTest extends HttpTest
{
    public function testIndexAction()
    {
        $this->assertSame('Hello, Dave.', (string)$this->get('/index')->getBody());
        $this->assertSame('Hello, Antony.', (string)$this->get('/index/Antony')->getBody());
    }

    public function testSession()
    {
        $this->assertSame('Hello, Dave.', (string)$this->get('/index')->getBody());
        $this->assertSame('Hello, Antony.', (string)$this->get('/index/Antony')->getBody());
    }
}