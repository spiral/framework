<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Translator;


use Spiral\Console\Command;
use Spiral\Translator\Catalogue\Manager;

class ResetCommand extends Command
{
    const NAME = 'i18n:reset';
    const DESCRIPTION = 'Reset translation cache';

    /**
     * @param Manager $manager
     */
    public function perform(Manager $manager)
    {
        $manager->reset();
        $this->writeln("Translation cache has been reset.");
    }
}