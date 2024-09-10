<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Traverse;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\NullableType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ConstructorParamsVisitor extends NodeVisitorAbstract
{
    /** @var array */
    private $params = [];

    /**
     * {@inheritDoc}
     */
    public function leaveNode(Node $node): void
    {
        if ($node instanceof ClassMethod && $node->name->name === '__construct') {
            foreach ($node->params as $param) {
                if ($param->type instanceof NullableType) {
                    if ($param->type->type instanceof Identifier) {
                        $type = $param->type->type->name;
                    } else {
                        $type = implode('\\', $param->type->type->parts);
                    }

                    $type = "?$type";
                } elseif ($param->type instanceof Name) {
                    $type = implode('\\', $param->type->parts);
                } else {
                    $type = $param->type->name ?? null;
                }

                $this->params[$param->var->name] = [
                    'type'     => $type,
                    'optional' => !empty($param->default),
                    'byRef'    => !empty($param->byRef),
                    'variadic' => !empty($param->variadic),
                ];
            }
        }
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
