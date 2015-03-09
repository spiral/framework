<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Encrypter;

use Spiral\Components\Encrypter\Encrypter;
use Spiral\Support\Tests\TestCase;
use Spiral\Tests\MemoryCore;

class RandomTest extends TestCase
{
    public function testRandom()
    {
        $encrypter = $this->createEncrypter(array(
            'key' => 'abc'
        ));

        $previousRandoms = array();
        for ($try = 0; $try < 100; $try++)
        {
            $random = $encrypter->random(32);
            $this->assertTrue(strlen($random) == 32);
            $this->assertNotContains($random, $previousRandoms);
            $previousRandoms[] = $random;
        }
    }

    protected function createEncrypter($config = array('key' => '1234567890123456'))
    {
        return new Encrypter(MemoryCore::getInstance()->setConfig('encrypter', $config));
    }
}