<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Views;

use Spiral\Tests\BaseTest;

class StemplerTest extends BaseTest
{
    public function testRenderSimple()
    {
        $this->assertSame('Hello, World!', trim($this->views->render('home', [
            'name' => 'World'
        ])));
    }
}