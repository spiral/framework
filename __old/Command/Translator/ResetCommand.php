<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Translator;


use Spiral\Console\Command;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Translator\Catalogue\CatalogueManager;

class ResetCommand extends Command implements SingletonInterface
{
    const NAME        = 'i18n:reset';
    const DESCRIPTION = 'Reset translation cache';

    /**
     * @param CatalogueManager $manager
     */
    public function perform(CatalogueManager $manager)
    {
        $manager->reset();
        $this->writeln("Translation cache has been reset.");
    }
}