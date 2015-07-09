<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Components\Encrypter;

use Spiral\Components\Encrypter\Encrypter;
use Spiral\Core\Configurator;
use Spiral\Support\Tests\TestCase;
use Spiral\Tests\MemoryCore;

class RandomTest extends TestCase
{
    /**
     * @param array $config
     * @return Encrypter
     */
    protected function getEncrypter($config = ['key' => '1234567890123456'])
    {
        return new Encrypter(
            new Configurator(['encrypter' => $config])
        );
    }

    public function testRandom()
    {
        $encrypter = $this->getEncrypter();

        $previousRandoms = [];
        for ($try = 0; $try < 100; $try++)
        {
            $random = $encrypter->random(32);
            $this->assertTrue(strlen($random) == 32);
            $this->assertNotContains($random, $previousRandoms);
            $previousRandoms[] = $random;
        }
    }
}