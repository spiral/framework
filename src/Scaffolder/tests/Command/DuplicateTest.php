<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use Throwable;

class DuplicateTest extends AbstractCommandTest
{
    /**
     * @throws Throwable
     */
    public function testScaffold(): void
    {
        $output = $this->console()->run('create:controller', [
            'name' => 'default'
        ]);

        $this->assertStringContainsString(
            "Unable to create 'DefaultController' declaration",
            $output->getOutput()->fetch()
        );
    }
}
