<?php

declare(strict_types=1);

namespace Spiral\Command\Tokenizer;

use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Console\Command;
use Spiral\Tokenizer\Attribute\AbstractTarget;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;

final class ValidateCommand extends Command
{
    protected const NAME = 'tokenizer:validate';
    protected const DESCRIPTION = 'Checks all listeners in the application to ensure they are correctly configured';

    public function perform(
        TokenizerListenerRegistryInterface $registry,
        DirectoriesInterface $dirs,
        ReaderInterface $reader
    ): int {
        $listeners = \method_exists($registry, 'getListenerClasses') ? $registry->getListenerClasses() : [];

        $grid = $this->table(['Listener', 'Suggestion']);
        foreach ($listeners as $class) {
            $ref = new \ReflectionClass($class);
            $attribute = $reader->firstClassMetadata($ref, AbstractTarget::class);
            $suggestion = match (true) {
                $attribute === null => 'Add <comment>#[TargetClass]</comment> or ' .
                    '<comment>#[TargetAttribute]</comment> attribute to the listener',
                default => '<fg=green> ✓ </>',
            };
            $grid->addRow([
                $class . "\n" . \sprintf('<fg=gray>%s</>', \str_replace($dirs->get('root'), '', $ref->getFileName())),
                $suggestion,
            ]);
        }

        $grid->render();

        return self::SUCCESS;
    }
}
