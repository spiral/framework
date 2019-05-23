<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Framework\GRPC;

use Spiral\Framework\ConsoleTest;

class GenerateTest extends ConsoleTest
{
    public function testGenerateNotFound()
    {
        $out = $this->runCommandDebug('grpc:generate', [
            'proto' => 'notfound'
        ]);

        $this->assertContains('not found', $out);
    }
}