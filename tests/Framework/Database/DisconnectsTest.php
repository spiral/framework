<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Database;

use Spiral\Boot\FinalizerInterface;
use Spiral\Bootloader\Database\DisconnectsBootloader;
use Cycle\Database\DatabaseInterface;
use Spiral\Tests\Framework\BaseTest;

class DisconnectsTest extends BaseTest
{
    public function testConnected(): void
    {
        $app = $this->makeApp();
        $db = $app->get(DatabaseInterface::class);

        $db->getTables();
        $this->assertTrue($db->getDriver()->isConnected());
    }

    public function testDisconnected(): void
    {
        $app = $this->makeApp();
        $db = $app->get(DatabaseInterface::class);

        $db->getTables();
        $this->assertTrue($db->getDriver()->isConnected());

        $app->getBootloadManager()->bootload([DisconnectsBootloader::class]);
        $this->assertTrue($db->getDriver()->isConnected());

        $app->get(FinalizerInterface::class)->finalize();

        $this->assertFalse($db->getDriver()->isConnected());
    }
}
