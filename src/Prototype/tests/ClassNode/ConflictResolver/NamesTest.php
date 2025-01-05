<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\ClassNode\ConflictResolver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Prototype\ClassNode;
use Spiral\Prototype\ClassNode\ConflictResolver\Names;
use Spiral\Tests\Prototype\ClassNode\ConflictResolver\Fixtures;
use Spiral\Tests\Prototype\Fixtures\Dependencies;

class NamesTest extends TestCase
{
    #[DataProvider('cdProvider')]
    public function testFind(string $method, array $vars, array $dependencies, array $expected): void
    {
        $cd = ClassNode::create('class\name');
        $cd->constructorVars = $vars;

        foreach (Fixtures\Params::getParams($method) as $param) {
            $cd->addParam($param);
        }

        $cd->dependencies = Dependencies::convert($dependencies);
        $this->names()->resolve($cd);

        $resolved = [];
        foreach ($cd->dependencies as $dependency) {
            $resolved[] = $dependency->var;
        }

        self::assertEquals($expected, $resolved);
    }

    public static function cdProvider(): \Traversable
    {
        yield [
            'paramsSource',
            [],
            ['v2' => 'type1', 'v' => 'type2', 'vv' => 'type3',],
            ['v2', 'v', 'vv']
        ];
        yield [
            'paramsSource',
            ['v', 'v2'],
            [
                'v2' => 'type1',
                'v' => 'type2',
                'vv' => 'type3',
                't1' => 'type4',
                't2' => 'type4',
                't4' => 'type4',
                't6' => 'type4'
            ],
            ['v3', 'v4', 'vv', 't', 't2', 't3', 't6']
        ];
        yield [
            'paramsSource3',
            [],
            ['t' => 'type', 't3' => 'type3'],
            ['t2', 't3']
        ];
    }

    private function names(): Names
    {
        $container = new Container();

        return $container->get(Names::class);
    }
}
