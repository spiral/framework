<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Framework\Encrypter;

use Spiral\Framework\ConsoleTest;

class KeyCommandTest extends ConsoleTest
{
    public function testKey()
    {
        $key = $this->runCommand('encrypt:key');
        $this->assertNotEmpty($key);
    }
}