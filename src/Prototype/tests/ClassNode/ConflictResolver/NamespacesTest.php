<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\ClassNode\ConflictResolver;

use Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures\Params;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Prototype\ClassNode;
use Spiral\Prototype\ClassNode\ConflictResolver\Namespaces;
use Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures;
use Spiral\Tests\Prototype\Fixtures\Dependencies;

class NamespacesTest extends TestCase
{
    #[DataProvider('cdProvider')]
    public function testFind(array $stmts, array $dependencies, array $expected): void
    {
        $cd = ClassNode::create('class\name');

        foreach ($stmts as $alias => $name) {
            $cd->addImportUsage($name, $alias);
        }

        foreach (Params::getParams('paramsSource') as $param) {
            $cd->addParam($param);
        }

        $cd->dependencies = Dependencies::convert($dependencies);
        $this->namespaces()->resolve($cd);

        $resolved = [];
        foreach ($cd->dependencies as $dependency) {
            $resolved[$dependency->property] = $dependency->type->getAliasOrShortName();
        }

        $this->assertEquals($expected, $resolved);
    }

    public static function cdProvider(): \Traversable
    {
        yield [
            //no conflicts
            [],
            [
                'v1' => 'a\\b\\c\\type1',
                'v2' => 'a\\b\\type2',
                'v3' => 'a\\b\\c\\type3'
            ],
            [
                'v1' => 'type1',
                'v2' => 'type2',
                'v3' => 'type3'
            ]
        ];
        yield [
            [
                'Test'      => 'a\\b\\Test',
                'TestAlias' => 'a\\b\\Test',
                'type1'     => 'a\\b\\c\\type1',
                'type2'     => 'a\\b\\c\\type2',
                'type7'     => 'a\\b\\c\\type4',
            ],
            //has conflicts
            [
                'v1' => 'a\\b\\c\\type1',
                'v2' => 'a\\b\\type1',
                'v3' => 'a\\b\\c\\type2',
                'v4' => 'a\\b\\type2',
                'v5' => 'a\\b\\c\\type7',
                'v6' => 'a\\b\\c\\type4',
                'v7' => 'a\\b\\type4',
                'v8' => 'a\\b\\type5',
            ],
            [
                'v1' => 'type1',
                'v2' => 'type',
                'v3' => 'type2',
                'v4' => 'type3',
                'v5' => 'type4',
                'v6' => 'type7',
                'v7' => 'type5',
                'v8' => 'type6',
            ]
        ];
        yield [
            [],
            //has conflicts
            [
                'v1' => 'a\\b\\type',
                'v2' => 'a\\b\\c\\type',
                'v3' => 'a\\b\\c\\type3',
            ],
            [
                'v1' => 'type',
                'v2' => 'type2',
                'v3' => 'type3',
            ]
        ];
    }

    private function namespaces(): Namespaces
    {
        $container = new Container();

        return $container->get(Namespaces::class);
    }
}
