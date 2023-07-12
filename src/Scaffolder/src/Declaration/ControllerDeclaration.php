<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Psr\Http\Message\ResponseInterface;
use Spiral\Prototype\Traits\PrototypeTrait;
use Spiral\Reactor\Partial\Method;
use Spiral\Router\Annotation\Route;

/**
 * Declares controller.
 */
class ControllerDeclaration extends AbstractDeclaration implements HasInstructions
{
    public const TYPE = 'controller';

    public function addAction(string $action): Method
    {
        return $this->class
            ->addMethod($action)
            ->addComment(
                'Please, don\'t forget to configure the Route attribute or remove it and register the route manually.'
            )
            ->setPublic()
            ->addAttribute(Route::class, ['route' => 'path', 'name' => 'name'])
            ->setReturnType(ResponseInterface::class);
    }

    public function addPrototypeTrait(): void
    {
        $this->namespace->addUse(PrototypeTrait::class);
        $this->class->addTrait(PrototypeTrait::class);
    }

    public function declare(): void
    {
        $this->namespace->addUse(Route::class);
        $this->namespace->addUse(ResponseInterface::class);
    }

    public function getInstructions(): array
    {
        return [
            'Read more about Controllers in the documentation: https://spiral.dev/docs/http-routing',
        ];
    }
}
