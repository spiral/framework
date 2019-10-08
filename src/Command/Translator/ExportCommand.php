<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Command\Translator;

use Spiral\Console\Command;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Translator\Catalogue\CatalogueManager;
use Spiral\Translator\CatalogueInterface;
use Spiral\Translator\Config\TranslatorConfig;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Translation\MessageCatalogue;

final class ExportCommand extends Command implements SingletonInterface
{
    public const NAME        = 'i18n:export';
    public const DESCRIPTION = 'Dump given locale using specified dumper and path';

    public const ARGUMENTS = [
        ['locale', InputArgument::REQUIRED, 'Locale to be dumped'],
        ['path', InputArgument::REQUIRED, 'Export path']
    ];

    public const OPTIONS = [
        ['dumper', 'd', InputOption::VALUE_OPTIONAL, 'Dumper name', 'php'],
        ['fallback', 'f', InputOption::VALUE_NONE, 'Merge messages from fallback catalogue'],
    ];

    /**
     * @param TranslatorConfig $config
     * @param CatalogueManager $manager
     */
    public function perform(TranslatorConfig $config, CatalogueManager $manager): void
    {
        if (!$config->hasDumper($this->option('dumper'))) {
            $this->writeln("<fg=red>Undefined dumper '{$this->option('dumper')}'.</fg=red>");

            return;
        }

        $mc = $this->getMessageCatalogue(
            $config,
            $manager,
            $manager->get($this->argument('locale'))
        );

        if ($this->isVerbose() && !empty($mc->getDomains())) {
            $this->sprintf(
                "<info>Exporting domain(s):</info> %s\n",
                join(',', $mc->getDomains())
            );
        }

        $dumper = $config->getDumper($this->option('dumper'));

        $dumper->dump($mc, [
            'path'           => $this->argument('path'),
            'default_locale' => $config->getDefaultLocale(),
            'xliff_version'  => '2.0' // forcing default version for xliff dumper only
        ]);

        $this->writeln('Export successfully completed using <info>' . get_class($dumper) . '</info>');
        $this->writeln('Output: <comment>' . realpath($this->argument('path')) . '</comment>');
    }

    /**
     * @param TranslatorConfig   $config
     * @param CatalogueManager   $manager
     * @param CatalogueInterface $catalogue
     * @return MessageCatalogue
     */
    protected function getMessageCatalogue(
        TranslatorConfig $config,
        CatalogueManager $manager,
        CatalogueInterface $catalogue
    ): MessageCatalogue {
        $messageCatalogue = new MessageCatalogue(
            $catalogue->getLocale(),
            $catalogue->getData()
        );

        if ($this->option('fallback')) {
            foreach ($manager->get($config->getFallbackLocale())->getData() as $domain => $messages) {
                foreach ($messages as $id => $message) {
                    if (!$messageCatalogue->defines($id, $domain)) {
                        $messageCatalogue->set($id, $message, $domain);
                    }
                }
            }
        }

        return $messageCatalogue;
    }
}
