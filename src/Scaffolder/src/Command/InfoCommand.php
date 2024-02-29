<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;
use Spiral\Console\Console;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Symfony\Component\Console\Helper\TableSeparator;

#[AsCommand(name: 'scaffolder:info', description: 'Show information about available scaffolder commands.')]
final class InfoCommand extends Command
{
    #[Argument(description: 'Class name')]
    private string $name = 'Example';

    public function perform(ScaffolderConfig $config, DirectoriesInterface $dirs, Console $console): int
    {
        $this->output->title('Scaffolder commands');
        $this->writeln(
            'Scaffolder enables developers to quickly and easily generate application code for various classes, using a set of console commands',
        );

        $this->newLine();

        $table = $this->table(['Command', 'Target']);
        $rootDir = $dirs->get('root');
        $available = $config->getDeclarations();

        $i = 0;
        foreach ($available as $name) {
            $command = 'create:' . $name;

            if (!$console->getApplication()->has($command)) {
                continue;
            }

            $command = $console->getApplication()->get($command);

            if ($i > 0) {
                $table->addRow(new TableSeparator());
            }
            $declaration = $config->getDeclaration($name);

            $options = [];
            foreach ($declaration['options'] ?? [] as $key => $value) {
                $options[] = $key . ': <fg=yellow>' . \json_encode(\str_replace($rootDir, '', $value)) . '</>';
            }

            $file = \str_replace($rootDir, '', $config->classFilename($name, $this->name));
            $namespace = $config->classNamespace($name, $this->name);
            $table->addRow([
                $command->getName() . "\n<fg=gray>{$command->getDescription()}</>",
                <<<TARGET
path: <fg=green>/$file</>
namespace: <fg=yellow>$namespace</>
TARGET
                .
                ($options !== [] ? "\n" . \implode("\n", $options) : ''),
            ]);

            $i++;
        }

        $randomName = $available[\array_rand($available)];
        $this->writeln(
            "<info>Use `<fg=yellow>php app.php create:{$randomName} {$this->name}</>` command to generate desired class. Below is a list of available commands:</info>",
        );

        $table->render();

        $this->writeln(
            '<info>Use `<fg=yellow>php app.php create:*** --help</>` command to see available options.</info>',
        );

        $this->writeln('Read more about scaffolder in <fg=yellow>https://spiral.dev/docs/basics-scaffolding</> documentation section.');

        return self::SUCCESS;
    }
}
