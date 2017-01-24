<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Support;

use Spiral\Support\ExceptionHelper;
use Spiral\Tests\BaseTest;

class ExceptionHelperTest extends BaseTest
{
    public function testGetMessage()
    {
        $ex = new \ErrorException("Unable to do something valuable");

        $this->assertContains(
            "ErrorException: Unable to do something valuable",
            ExceptionHelper::createMessage($ex)
        );
    }

    public function testHighlight()
    {
        $this->assertContains(
            'testHighlight',
            ExceptionHelper::highlightSource(__FILE__, __LINE__)
        );
    }
}