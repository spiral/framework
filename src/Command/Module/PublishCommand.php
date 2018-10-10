<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Module;

use Spiral\Console\Command;
use Spiral\Module\Publisher;
use Symfony\Component\Console\Input\InputArgument;

class PublishCommand extends Command
{
    const NAME        = 'publish';
    const DESCRIPTION = 'Publish resources';

    /**
     * {@inheritdoc}
     */
    const ARGUMENTS = [
        ['type', InputArgument::REQUIRED, 'Operation type [replace|follow|ensure]'],
        ['target', InputArgument::REQUIRED, 'Target file or directory'],
        ['source', InputArgument::OPTIONAL, 'Source file or directory'],
        ['mode', InputArgument::OPTIONAL, 'runtime', 'File mode [readonly|runtime]'],
    ];

    /**
     * @param Publisher $publisher
     */
    public function perform(Publisher $publisher)
    {
//        switch ($this->argument('mode')) {
//            case 'replace':
//            case 'follow':
//            case 'ensure':
//
//        }

        dumP($this->argument('source'));
        dumP($this->argument('target'));
        dumP($this->argument('mode'));
        dumP($this->argument('command'));
    }
}