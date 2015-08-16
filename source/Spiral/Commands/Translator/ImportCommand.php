<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Translator;

use Spiral\Console\Command;
use Spiral\Translator\Importers\GetTextImporter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Import language strings from spiral specific PO file (see i18n:export command).
 */
class ImportCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'i18n:import';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Import GetText PO file to application bundles.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['filename', InputArgument::REQUIRED, 'Input filename.'],
    ];

    /**
     * {@inheritdoc}
     */
    protected $options = [
        ['language', 'l', InputOption::VALUE_OPTIONAL, 'Target language.', 'auto'],
    ];

    /**
     * Perform command.
     *
     * @param GetTextImporter $importer
     */
    public function perform(GetTextImporter $importer)
    {
        $this->writeln(
            "Importing PO file '<comment>{$this->argument('filename')}</comment>'."
        );

        $importer->open($this->argument('filename'));
        if ($this->option('language') != 'auto') {
            $importer->setLanguage($this->option('language'));
        }

        $importer->import();

        $this->writeln(
            "<info>Import completed, target language '<comment>{$importer->getLanguage()}</comment>'.</info>"
        );
    }
}