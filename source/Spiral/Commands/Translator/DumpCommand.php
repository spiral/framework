<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Translator;

use Spiral\Console\Command;
use Spiral\Translator\Configs\TranslatorConfig;
use Spiral\Translator\Translator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Translation\Dumper\DumperInterface;
use Symfony\Component\Translation\Dumper\FileDumper;

/**
 * Index available classes and function calls to fetch every used string translation. Can
 * understand l, p and translate (trait) function.
 *
 * @see Indexer
 */
class DumpCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'i18n:dump';

    /**
     * {@inheritdoc}
     */
    const DESCRIPTION = 'Dump given locale using specified dumper and path';

    /**
     * {@inheritdoc}
     */
    const ARGUMENTS = [
        ['locale', InputArgument::REQUIRED, 'Locale to be dumped'],
        ['path', InputArgument::REQUIRED, 'Export path']
    ];

    /**
     * {@inheritdoc}
     */
    const OPTIONS = [
        ['dumper', 'd', InputOption::VALUE_OPTIONAL, 'Dumper name', 'php'],
        ['fallback', 'f', InputOption::VALUE_NONE, 'Merge messages from fallback locale'],
    ];

    /**
     * @param TranslatorConfig $config
     * @param Translator       $translator
     *
     * @return void
     */
    public function perform(TranslatorConfig $config, Translator $translator)
    {
        if (!$config->hasDumper($this->option('dumper'))) {
            $this->writeln("<fg=red>Undefined dumper '{$this->option('dumper')}'.</fg=red>");

            return null;
        }

        $catalogue = $translator->getCatalogue($this->argument('locale'))->loadDomains();

        if ($this->option('fallback')) {
            //Let's merge with fallback locale
            $fallbackCatalogue = $translator->getCatalogue($config->fallbackLocale())->loadDomains();

            //Soft merge
            $catalogue->mergeFrom($fallbackCatalogue->toMessageCatalogue(), false);
        }

        //Pre-loading all domains
        $messageCatalogue = $catalogue->toMessageCatalogue();

        if ($this->isVerbosity() && !empty($messageCatalogue->getDomains())) {
            $this->writeln(
                "<info>Dumping domain(s):</info> " . join(',', $messageCatalogue->getDomains())
            );
        }

        $dumper = $config->dumperClass($this->option('dumper'));

        /**
         * @var DumperInterface $dumper
         */
        $dumper = new $dumper;

        if ($dumper instanceof FileDumper) {
            //Symfony why are you breaking compatibility in internal API?
            $dumper->setBackup(false);
        }

        $dumper->dump($messageCatalogue, [
            'path'           => $this->argument('path'),
            'default_locale' => $config->defaultLocale(),

            //Forcing default version for xliff dumper only
            'xliff_version'  => '2.0'
        ]);

        $this->writeln("Dump successfully completed using <info>" . get_class($dumper) . "</info>");
    }
}
