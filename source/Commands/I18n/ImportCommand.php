<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\I18n;

use Spiral\Components\Console\Command;
use Spiral\Components\I18n\GetText\Importer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ImportCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'i18n:import';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Import GetText PO file to application bundles.';

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments()
     * method.
     *
     * @var array
     */
    protected $arguments = array(
        ['filename', InputArgument::REQUIRED, 'Input filename.'],
    );

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions()
     * method.
     *
     * @var array
     */
    protected $options = array(
        ['language', 'l', InputOption::VALUE_OPTIONAL, 'Target language.', 'auto'],
    );

    /**
     * Exporting to GetText format.
     */
    public function perform()
    {
        $this->writeln(
            "Importing PO file '<comment>{$this->argument('filename')}</comment>'."
        );

        $importer = Importer::make();
        $importer->openFile($this->argument('filename'));

        if ($this->option('language') != 'auto')
        {
            $importer->setLanguage($this->option('language'));
        }

        $importer->importBundles();

        $this->writeln(
            "<info>Import completed, target language "
            . "'<comment>{$importer->getLanguage()}</comment>'.</info>"
        );
    }
}