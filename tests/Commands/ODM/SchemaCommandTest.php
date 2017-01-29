<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Commands\ODM;

use Spiral\Tests\BaseTest;

class SchemaCommandTest extends BaseTest
{
    public function testSchema()
    {
        $output = $this->console->run('odm:schema');
        $this->assertSame(0, $output->getCode());

        $output = $output->getOutput()->fetch();

        $this->assertContains('found documents', $output);
        $this->assertContains('Silent mode', $output);
    }

    public function testSchemaWithIndexes()
    {
        $output = $this->console->run('odm:schema', [
            '--indexes' => true
        ]);

        $this->assertSame(0, $output->getCode());

        $output = $output->getOutput()->fetch();

        $this->assertContains('found documents', $output);
        $this->assertNotContains('Silent mode', $output);
    }
}