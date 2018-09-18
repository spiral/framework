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
use Spiral\Translator\Catalogue\Manager;
use Spiral\Translator\Config\TranslatorConfig;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Translation\MessageCatalogue;

class ExportCommand extends Command implements SingletonInterface
{
    const NAME = "i18n:export";
    const DESCRIPTION = 'Dump given locale using specified dumper and path';

    const ARGUMENTS = [
        ['locale', InputArgument::REQUIRED, 'Locale to be dumped'],
        ['path', InputArgument::REQUIRED, 'Export path']
    ];

    const OPTIONS = [
        ['dumper', 'd', InputOption::VALUE_OPTIONAL, 'Dumper name', 'php']
    ];

    /**
     * @param TranslatorConfig $config
     * @param Manager          $manager
     *
     * @return void
     */
    public function perform(TranslatorConfig $config, Manager $manager)
    {
        if (!$config->hasDumper($this->option('dumper'))) {
            $this->writeln("<fg=red>Undefined dumper '{$this->option('dumper')}'.</fg=red>");

            return null;
        }

        $catalogue = $manager->get($this->argument('locale'));

        $messageCatalogue = new MessageCatalogue(
            $catalogue->getLocale(),
            $catalogue->getData()
        );

        if ($this->isVerbose() && !empty($messageCatalogue->getDomains())) {
            $this->sprintf("<info>Exporting domain(s):</info> %s\n", join(',', $messageCatalogue->getDomains())
            );
        }

        $dumper = $config->getDumper($this->option('dumper'));

        $dumper->dump($messageCatalogue, [
            'path'           => $this->argument('path'),
            'default_locale' => $config->defaultLocale(),
            //Forcing default version for xliff dumper only
            'xliff_version'  => '2.0'
        ]);

        $this->writeln("Export successfully completed using <info>" . get_class($dumper) . "</info>");
        $this->writeln("Output: <comment>" . realpath($this->argument('path')) . "</comment>");
    }
}