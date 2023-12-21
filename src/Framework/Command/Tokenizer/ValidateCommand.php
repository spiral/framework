<?php

declare(strict_types=1);

namespace Spiral\Command\Tokenizer;

use Spiral\Attributes\ReaderInterface;
use Spiral\Console\Command;
use Spiral\Tokenizer\Attribute\AbstractTarget;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\TokenizationListenerInterface;

final class ValidateCommand extends Command
{
    protected const NAME = 'tokenizer:validate';
    protected const DESCRIPTION = 'Checks all listeners in the application to ensure they are correctly configured';

    public function perform(ClassesInterface $classes, ReaderInterface $reader): int
    {
        $invalid = [];
        foreach ($classes->getClasses() as $class) {
            if (!$class->implementsInterface(TokenizationListenerInterface::class)) {
                continue;
            }
            $attribute = $reader->firstClassMetadata($class, AbstractTarget::class);
            if ($attribute === null) {
                $invalid[$class->getName()] = 'Add #[TargetClass] or #[TargetAttribute] attribute to the listener';
            }
        }

        if ($invalid === []) {
            $this->info('All listeners are correctly configured.');

            return self::SUCCESS;
        }

        $grid = $this->table(['Listener', 'Suggestion']);

        foreach ($invalid as $listener => $message) {
            $grid->addRow([$listener, $message]);
        }

        $grid->render();

        return self::SUCCESS;
    }
}
