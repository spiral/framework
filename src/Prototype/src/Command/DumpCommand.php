<?php

declare(strict_types=1);

namespace Spiral\Prototype\Command;

use Spiral\Prototype\Annotation;
use Spiral\Prototype\Bootloader\PrototypeBootloader;
use Spiral\Prototype\Traits\PrototypeTrait;

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
    public function perform(PrototypeBootloader $prototypeBootloader): int
    {
        $dependencies = $this->registry->getPropertyBindings();
        if ($dependencies === []) {
            $this->writeln('<comment>No prototyped shortcuts found.</comment>');

            return self::SUCCESS;
        }

        $this->write('Updating <fg=yellow>PrototypeTrait</fg=yellow> DOCComment... ');

        $trait = new \ReflectionClass(PrototypeTrait::class);
        $docComment = $trait->getDocComment();
        if ($docComment === false) {
            $this->write('<fg=reg>DOCComment is missing</fg=red>');

            return self::FAILURE;
        }

        $filename = $trait->getFileName();

        try {
            \file_put_contents(
                $filename,
                \str_replace(
                    $docComment,
                    $this->buildAnnotation($dependencies),
                    \file_get_contents($filename)
                )
            );
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

    private function buildAnnotation(array $dependencies): string
    {
        $an = new Annotation\Parser('');
        $an->lines[] = new Annotation\Line(
            'This DocComment is auto-generated, do not edit or commit this file to repository.'
        );
        $an->lines[] = new Annotation\Line('');

        foreach ($dependencies as $dependency) {
            $an->lines[] = new Annotation\Line(
                \sprintf('\\%s $%s', $dependency->type->fullName, $dependency->var),
                'property'
            );
        }

        return $an->compile();
    }
}
