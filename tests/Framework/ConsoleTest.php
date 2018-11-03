<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework;

use Spiral\App\TestApp;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class ConsoleTest extends BaseTest
{
    /** @var TestApp */
    protected $app;

    public function setUp()
    {
        $this->app = $this->makeApp();
    }

    public function runCommand(string $command, array $args = []): string
    {
        $input = new ArrayInput($args);
        $output = new BufferedOutput();

        $this->app->console()->run($command, $input, $output);

        return $output->fetch();
    }
}