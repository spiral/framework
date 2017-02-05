<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Tests\Encrypter;

use Spiral\Encrypter\Configs\EncrypterConfig;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testKey()
    {
        $config = new EncrypterConfig([
            'key' => 'abc'
        ]);

        $this->assertSame('abc', $config->getKey());
    }
}