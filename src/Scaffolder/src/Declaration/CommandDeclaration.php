<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Attribute\Question;
use Spiral\Console\Command;
use Spiral\Scaffolder\Config\ScaffolderConfig;

final class CommandDeclaration extends AbstractDeclaration implements HasInstructions
{
    public const TYPE = 'command';

    public function __construct(
        ScaffolderConfig $config,
        string $name,
        ?string $comment = null,
        ?string $namespace = null,
        private readonly ?string $alias = null,
        private readonly ?string $description = null,
    ) {
        parent::__construct($config, $name, $comment, $namespace);
    }

    public function addArgument(string $name): void
    {
        $this->class
            ->addProperty($name)
            ->setPrivate()
            ->setType('string')
            ->addAttribute(Argument::class, [
                'description' => 'Argument description',
            ])
            ->addAttribute(Question::class, [
                'question' => \sprintf('What would you like to name the %s argument?', $name),
            ]);
    }

    public function addOption(string $name): void
    {
        $this->class
            ->addProperty($name)
            ->setPrivate()
            ->setType('bool')
            ->addAttribute(Option::class, [
                'description' => 'Argument description',
            ]);
    }

    /**
     * Declare default command body.
     */
    public function declare(): void
    {
        $this->namespace->addUse(Command::class);
        $this->namespace->addUse(AsCommand::class);
        $this->namespace->addUse(Argument::class);
        $this->namespace->addUse(Option::class);
        $this->namespace->addUse(Question::class);

        $this->class->setExtends(Command::class);
        $this->class->setFinal();

        $commandDefinition = [
            'name' => $this->alias,
        ];

        if ($this->description) {
            $commandDefinition['description'] = $this->description;
        }

        $this->class->addAttribute(AsCommand::class, $commandDefinition);

        $this->class
            ->addMethod('__invoke')
            ->setReturnType('int')
            ->setBody(
                <<<'PHP'
                // Put your command logic here
                $this->info('Command logic is not implemented yet');

                return self::SUCCESS;
                PHP,
            );
    }

    public function getInstructions(): array
    {
        return [
            \sprintf('Use the following command to run your command: \'<comment>php app.php %s</comment>\'', $this->alias),
            'Read more about user Commands in the documentation: https://spiral.dev/docs/console-commands',
        ];
    }
}
