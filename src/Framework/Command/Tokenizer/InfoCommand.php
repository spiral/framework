<?php

declare(strict_types=1);

namespace Spiral\Command\Tokenizer;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;
use Spiral\Tokenizer\Config\TokenizerConfig;

#[AsCommand(
    name: 'tokenizer:info',
    description: 'Get information about tokenizer directories to scan'
)]
final class InfoCommand extends Command
{
    public function perform(TokenizerConfig $config, DirectoriesInterface $dirs): int
    {
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
        $this->info(
            \sprintf('Tokenizer cache: %s', $config->isCacheEnabled() ? '<success>enabled</>' : '<error>disabled</>'),
        );
        if (!$config->isCacheEnabled()) {
            $this->comment('To enable cache, add "TOKENIZER_CACHE_TARGETS=true" to your .env file.');
            $this->comment('Read more at https://spiral.dev/docs/advanced-tokenizer/#class-listeners');
        }

        return self::SUCCESS;
    }
}
