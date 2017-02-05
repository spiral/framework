<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Translator;

use Spiral\Console\Command;
use Spiral\Translator\Translator;

/**
 * Force translator to reload locale domains from application files.
 */
class ReloadCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'i18n:reload';

    /**
     * {@inheritdoc}
     */
    const DESCRIPTION = 'Force Translator to reload locales';

    /**
     * @param Translator $translator
     */
    public function perform(Translator $translator)
    {
        $translator->flushLocales();
        $translator->loadLocales();

        $this->writeln("Translation cache has been reloaded.");
    }
}