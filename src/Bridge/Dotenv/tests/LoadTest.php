<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Tests\DotEnv;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\Directories;
use Spiral\Boot\Environment;
use Spiral\DotEnv\Bootloader\DotenvBootloader;

class LoadTest extends TestCase
{
    public function testNotFound()
    {
        $e = new Environment();
        $d = new Directories(['root' => __DIR__ . '/..']);

        $b = new DotenvBootloader();
        $b->boot($d, $e);

        $this->assertNull($e->get('KEY'));
    }

    public function testFound()
    {
        $e = new Environment();
        $d = new Directories(['root' => __DIR__]);

        $b = new DotenvBootloader();
        $b->boot($d, $e);

        $this->assertSame('value', $e->get('KEY'));
    }

    public function testFoundCustom()
    {
        $e = new Environment([
            'DOTENV_PATH' => __DIR__ . '/.env.custom'
        ]);

        $d = new Directories(['root' => __DIR__]);

        $b = new DotenvBootloader();
        $b->boot($d, $e);

        $this->assertSame('custom_value', $e->get('KEY'));
    }
}
