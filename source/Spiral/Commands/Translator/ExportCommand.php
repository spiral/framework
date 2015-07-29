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
use Spiral\Translator\Exporters\GetTextExporter;
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
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments()
     * method.
     *
     * @var array
     */
    protected $arguments = [
        ['filename', InputArgument::REQUIRED, 'Output filename.'],
    ];

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions()
     * method.
     *
     * @var array
     */
    protected $options = [
        ['language', 'l', InputOption::VALUE_OPTIONAL, 'Source language.', 'en'],
        ['prefix', 'p', InputOption::VALUE_OPTIONAL, 'Only bundles starts with prefix.', '']
    ];

    /**
     * Exporting to GetText format.
     */
    public function perform()
    {
        $this->writeln(
            "Exporting '<comment>{$this->option('language')}</comment>' language bundles to PO file."
        );

        /**
         * @var GetTextExporter $exporter
         */
        $exporter = $this->getContainer()->get(GetTextExporter::class);

        $exporter->load(
            $this->option('language'), $this->option('prefix')
        )->export($this->argument('filename'));

        $this->writeln("<info>Export completed:</info> {$this->argument('filename')}");
    }
}