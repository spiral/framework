<?php

declare(strict_types=1);

namespace Spiral\Prototype\Command;

use Spiral\Prototype\Traits\PrototypeTrait;
use Spiral\Reactor\FileDeclaration;
use Spiral\Reactor\TraitDeclaration;
use Spiral\Reactor\Writer;

final class DumpCommand extends AbstractCommand
{
    public const NAME = 'prototype:dump';
    public const DESCRIPTION = 'Dump all prototyped dependencies as PrototypeTrait DOCComment.';
    public const OPTIONS = [];

    /**
     * Show list of available shortcuts and update trait docComment.
     *
     * @throws \ReflectionException
     */
    public function perform(Writer $writer): int
    {
        $dependencies = $this->getRegistry()->getPropertyBindings();
        if ($dependencies === []) {
            $this->comment('No prototyped shortcuts found.');

            return self::SUCCESS;
        }

        $this->write('Updating <fg=yellow>PrototypeTrait</fg=yellow> DOCComment... ');

        $ref = new \ReflectionClass(PrototypeTrait::class);
        $file = FileDeclaration::fromReflection($ref);
        $trait = $file->getTrait(PrototypeTrait::class);

        try {
            $this->buildAnnotation($trait, $dependencies);

            $writer->write($ref->getFileName(), $file);
        } catch (\Throwable $e) {
            $this->write('<fg=red>' . $e->getMessage() . "</fg=red>\n");

            return self::FAILURE;
        }

        $this->write("<fg=green>complete</fg=green>\n");

        if ($this->isVerbose()) {
            $grid = $this->table(['Property:', 'Target:']);

            foreach ($dependencies as $dependency) {
                $grid->addRow([$dependency->var, $dependency->type->fullName]);
            }

            $grid->render();
        }

        return self::SUCCESS;
    }

    private function buildAnnotation(TraitDeclaration $trait, array $dependencies): void
    {
        $trait->setComment(null);
        $trait->addComment('This DocComment is auto-generated, do not edit or commit this file to repository.');
        $trait->addComment('');

        foreach ($dependencies as $dependency) {
            $trait->addComment(\sprintf('@property \\%s $%s', $dependency->type->fullName, $dependency->var));
        }
    }
}
