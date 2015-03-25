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
use Spiral\Components\I18n\GetText\Exporter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ExportCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'i18n:export';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Export specified language to GetText PO file.';

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments() method.
     *
     * @var array
     */
    protected $arguments = array(
        ['filename', InputArgument::REQUIRED, 'Output filename.'],
    );

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions() method.
     *
     * @var array
     */
    protected $options = array(
        ['language', 'l', InputOption::VALUE_OPTIONAL, 'Source language.', 'en'],
        ['prefix', 'p', InputOption::VALUE_OPTIONAL, 'Only bundles starts with prefix.', '']
    );

    /**
     * Exporting to GetText format.
     */
    public function perform()
    {
        $this->writeln("Exporting '<comment>{$this->option('language')}</comment>' language bundles to PO file.");

        $exporter = Exporter::make();
        $exporter->loadLanguage($this->option('language'), $this->option('prefix'));
        $exporter->exportBundles($this->argument('filename'));

        $this->writeln("<info>Export completed:</info> {$this->argument('filename')}");
    }
}