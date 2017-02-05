<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Database;

use Spiral\Tests\BaseTest;

class SessionsTest extends BaseTest
{
    public function testTry()
    {
        $this->db->getDriver()->connect();
    }

    public function testTry2()
    {
        $this->db->getDriver()->connect();
    }
}