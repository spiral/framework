<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Jobs;

use Spiral\Console\Command;

class ListCommand extends Command
{
    const NAME        = "jobs:list";
    const DESCRIPTION = "List all available jobs";
}