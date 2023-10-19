<?php

declare(strict_types=1);

namespace Spiral\Prototype\Command;

final class ListCommand extends AbstractCommand
{
    public const NAME = 'prototype:list';
    public const DESCRIPTION = 'List all declared prototype dependencies';

    public function perform(): int
    {
        $bindings = $this->getRegistry()->getPropertyBindings();
        if ($bindings === []) {
            $this->comment('No prototype dependencies found.');

            return self::SUCCESS;
        }

        $grid = $this->table(['Name:', 'Target:']);

        foreach ($bindings as $binding) {
            $grid->addRow([$binding->property, $binding->type->name()]);
        }

        $grid->render();

        return self::SUCCESS;
    }
}
