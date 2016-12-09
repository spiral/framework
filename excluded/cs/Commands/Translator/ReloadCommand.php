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
    protected $name = 'i18n:reload';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Force Translator to reload locales';

    /**
     * @param Translator $translator
     */
    public function perform(Translator $translator)
    {
        $translator->flushLocales()->loadLocales();

        $this->writeln("Translation cache has been reloaded.");
    }
}