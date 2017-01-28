<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Console;

use Spiral\Console\Helpers\AskHelper;
use Spiral\Tests\BaseTest;
use Spiral\Tests\Console\Fixtures\EmptyCommand;

class HelpersTest extends BaseTest
{
    public function testWeird()
    {
        $command = new EmptyCommand($this->container);

        $this->assertSame('empty', $command->getName());
        $this->assertSame('description', $command->getDescription());

        $ask = $command->getAsk();
        $this->assertInstanceOf(AskHelper::class, $ask);
    }
}