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

/**
 * Export specific language into PO file using spiral hooks and PO comments.
 */
class ExportCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'i18n:export';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Export specified language to GetText PO file.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['filename', InputArgument::REQUIRED, 'Output filename.'],
    ];

    /**
     * {@inheritdoc}
     */
    protected $options = [
        ['language', 'l', InputOption::VALUE_OPTIONAL, 'Source language.', 'en'],
        ['prefix', 'p', InputOption::VALUE_OPTIONAL, 'Only bundles starts with prefix.', '']
    ];

    /**
     * Perform command.
     *
     * @param GetTextExporter $exporter
     */
    public function perform(GetTextExporter $exporter)
    {
        $this->writeln(
            "Exporting '<comment>{$this->option('language')}</comment>' language bundles to PO file."
        );

        $exporter->load($this->option('language'), $this->option('prefix'))->export(
            $this->argument('filename')
        );

        $this->writeln("<info>Export completed:</info> {$this->argument('filename')}");
    }
}