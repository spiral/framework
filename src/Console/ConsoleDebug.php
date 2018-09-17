<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Console;

use Spiral\Debug\LogsInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleDebug
{
    public function __construct(LogsInterface $logs=null)
    {
//        // todo: move into separate handler
//        if ($this->container->has(LogsInterface::class)) {
//            $logs = $this->container->get(LogsInterface::class);
//            if ($logs instanceof LogFactory) {
//                $logs->getEventDispatcher()->addListener("log", function (LogEvent $log) {
//                    echo $log->getMessage() . "\n";
//                });
//            }
//        }
    }

    public function withOutput(OutputInterface $output):self
    {}

    public function enable(){}
    public function disable(){}
}