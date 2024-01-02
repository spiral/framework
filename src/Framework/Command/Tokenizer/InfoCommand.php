<?php

declare(strict_types=1);

namespace Spiral\Command\Tokenizer;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Console\Command;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;

final class InfoCommand extends Command
{
    protected const NAME = 'tokenizer:info';
    protected const DESCRIPTION = 'Get information about tokenizer directories to scan';

    public function perform(
        TokenizerConfig $config,
        DirectoriesInterface $dirs,
        TokenizerListenerRegistryInterface $listenerRegistry
    ): int {
        $this->info('Included directories:');
        $grid = $this->table(['Directory', 'Scope']);
        foreach ($config->getDirectories() as $directory) {
            $grid->addRow([\str_replace($dirs->get('root'), '', $directory), '']);
        }
        foreach ($config->getScopes() as $scope => $data) {
            foreach ($data['directories'] ?? [] as $directory) {
                $grid->addRow([\str_replace($dirs->get('root'), '', $directory), $scope]);
            }
        }

        $grid->render();

        $this->newLine();

        $this->info('Excluded directories:');
        $grid = $this->table(['Directory', 'Scope']);
        foreach ($config->getExcludes() as $directory) {
            $grid->addRow([\str_replace($dirs->get('root'), '', $directory), '']);
        }
        foreach ($config->getScopes() as $scope => $data) {
            foreach ($data['exclude'] ?? [] as $directory) {
                $grid->addRow([\str_replace($dirs->get('root'), '', $directory), $scope]);
            }
        }

        $grid->render();

        $this->newLine();

        $this->info('Loaders:');
        $grid = $this->table(['Loader', 'Status']);

        $grid->addRow(['Classes', $config->isLoadClassesEnabled()
            ? '<info>enabled</>'
            : '<error>disabled</>. <comment>To enable, add "TOKENIZER_LOAD_CLASSES=true" to your .env file.</>']);
        $grid->addRow(['Enums', $config->isLoadEnumsEnabled()
            ? '<info>enabled</>'
            : '<error>disabled</>. <comment>To enable, add "TOKENIZER_LOAD_ENUMS=true" to your .env file.</>']);
        $grid->addRow(
            ['Interfaces', $config->isLoadInterfacesEnabled()
                ? '<info>enabled</>'
                : '<error>disabled</>. <comment>To enable, add "TOKENIZER_LOAD_INTERFACES=true" to your .env file.</>']
        );

        $grid->render();

        $listeners = \method_exists($listenerRegistry, 'getListenerClasses')
            ? $listenerRegistry->getListenerClasses()
            : [];

        $this->newLine();

        $this->info('Listeners:');
        $grid = $this->table(['Registered Listener', 'File']);
        foreach ($listeners as $listener) {
            $grid->addRow([
                $listener,
                \str_replace($dirs->get('root'), '', (new \ReflectionClass($listener))->getFileName()),
            ]);
        }

        $grid->render();

        $this->newLine();

        $this->newLine();
        $this->info(
            \sprintf('Tokenizer cache: %s', $config->isCacheEnabled() ? '<info>enabled</>' : '<error>disabled</>'),
        );
        if (!$config->isCacheEnabled()) {
            $this->comment('To enable cache, add "TOKENIZER_CACHE_TARGETS=true" to your .env file.');
            $this->comment('Read more at https://spiral.dev/docs/advanced-tokenizer/#class-listeners');
        }

        return self::SUCCESS;
    }
}
