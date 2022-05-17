<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Command;

use Spiral\Scaffolder\Declaration\ControllerDeclaration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ControllerCommand extends AbstractCommand
{
    protected const NAME        = 'create:controller';
    protected const DESCRIPTION = 'Create controller declaration';
    protected const ARGUMENTS   = [
        ['name', InputArgument::REQUIRED, 'Controller name'],
    ];
    protected const OPTIONS     = [
        [
            'action',
            'a',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'Pre-create controller action',
        ],
        [
            'prototype',
            'p',
            InputOption::VALUE_NONE,
            'Add \Spiral\Prototype\Traits\PrototypeTrait to controller',
        ],
        [
            'comment',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Optional comment to add as class header',
        ],
    ];

    /**
     * Create controller declaration.
     */
    public function perform(): int
    {
        $declaration = $this->createDeclaration(ControllerDeclaration::class);

        foreach ($this->option('action') as $action) {
            $declaration->addAction($action);
        }

        if ((bool)$this->option('prototype')) {
            $declaration->addPrototypeTrait();
        }

        $this->writeDeclaration($declaration);

        return self::SUCCESS;
    }
}
