<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Console;

use Spiral\Tests\BaseTest;
use Spiral\Translator\Indexer;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class VerbosityTest extends BaseTest
{
    public function testConfigureWithVerbosity()
    {
        //Make sure logged is emptied
        Indexer::shareLogger(null);

        $output = new BufferedOutput();
        $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);

        $this->console->run('configure', [], $output);
        $result = $output->fetch();

        //Expect to be added via loggers
        $this->assertContains(
            '[Indexer] Found [validation]: \'Condition \'{condition}\' does not meet.\'',
            $result
        );
    }
}