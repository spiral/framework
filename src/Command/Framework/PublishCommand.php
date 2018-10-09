<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Framework;

use Spiral\Console\Command;
use Spiral\Modules\Publisher;
use Symfony\Component\Console\Input\InputArgument;

class PublishCommand extends Command
{
    const NAME        = 'publish';
    const DESCRIPTION = 'Publish resources';

    /**
     * {@inheritdoc}
     */
    const ARGUMENTS = [
        ['command', InputArgument::REQUIRED, 'Publish command [dir|file|ensure]'],
        ['source', InputArgument::REQUIRED, 'Source file or directory'],
        ['target', InputArgument::REQUIRED, 'Target file or directory'],
        ['mode', InputArgument::OPTIONAL, 'runtime', 'File mode [readonly|runtime]'],
        ['merge', InputArgument::OPTIONAL, 'follow', 'Merge option [replace|follow]'],
    ];

    /**
     * @param Publisher $publisher
     */
    public function perform(Publisher $publisher)
    {

    }
}