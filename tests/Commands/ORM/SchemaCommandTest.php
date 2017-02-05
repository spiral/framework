<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Commands\ORM;

use Spiral\Tests\BaseTest;

class SchemaCommandTest extends BaseTest
{
    public function testSchema()
    {
        $output = $this->console->run('orm:schema');
        $this->assertSame(0, $output->getCode());

        $output = $output->getOutput()->fetch();

        $this->assertContains('records', $output);
        $this->assertContains('Silent mode', $output);
    }

    public function testSchemaWithAlter()
    {
        $this->assertEmpty($this->db->getTables());

        $output = $this->console->run('orm:schema', [
            '--alter' => true
        ]);

        $this->assertNotEmpty($this->db->getTables());

        $this->assertSame(0, $output->getCode());

        $output = $output->getOutput()->fetch();

        $this->assertContains('records', $output);
    }
}