<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Exceptions;

use Exception;
use PHPUnit\Framework\TestCase;
use Spiral\Exceptions\PlainHandler;

class PlainHandlerTest extends TestCase
{
    public function testRenderException(): void
    {
        $plainHandler = new PlainHandler();
        $result = $plainHandler->renderException(new Exception('Undefined variable $undefined'));

        $this->assertStringContainsString(
            sprintf('Undefined variable $undefined in %s', __FILE__),
            $result
        );
    }
}
