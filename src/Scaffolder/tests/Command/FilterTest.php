<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use ReflectionClass;
use ReflectionException;
use Spiral\Tests\Scaffolder\Command\Fixtures\SourceEntity;
use Throwable;

class FilterTest extends AbstractCommandTest
{
    private const CLASS_NAME = '\\Spiral\\Tests\\Scaffolder\\App\\Request\\SampleRequest';

    public function tearDown(): void
    {
        $this->deleteDeclaration(self::CLASS_NAME);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffold(): void
    {
        $this->console()->run('create:filter', [
            'name'    => 'sample',
            '--field' => [
                'name:string',
                'email:email',
                'upload:image',
                'unknown:unknown',
                'address',
                'age:string(query)',
                'datetime:datetime(query:date)',
            ]
        ]);

        clearstatcache();
        $this->assertTrue(class_exists(self::CLASS_NAME));

        $reflection = new ReflectionClass(self::CLASS_NAME);

        $this->assertStringContainsString('strict_types=1', $this->files()->read($reflection->getFileName()));
        $this->assertSame([
            'name'     => 'data:name',
            'email'    => 'data:email',
            'upload'   => 'file:upload',
            'unknown'  => 'data:unknown',
            'address'  => 'data:address',
            'age'      => 'query:age',
            'datetime' => 'query:date',
        ], $reflection->getConstant('SCHEMA'));
        $this->assertSame([
            'name'    => ['notEmpty', 'string'],
            'email'   => ['notEmpty', 'string', 'email'],
            'upload'  => ['image::uploaded', 'image::valid'],
            'address' => ['notEmpty', 'string'],
            'age'     => ['notEmpty', 'string'],
        ], $reflection->getConstant('VALIDATES'));
        $this->assertSame([], $reflection->getConstant('SETTERS'));
    }

    /**
     * @throws Throwable
     */
    public function testFromUnknownEntity(): void
    {
        $output = $this->console()->run('create:filter', [
            'name'     => 'sample',
            '--entity' => '\Some\Unknown\Entity'
        ]);

        $this->assertStringContainsString('Unable', $output->getOutput()->fetch());
    }

    /**
     * @throws Throwable
     */
    public function testFromEntity(): void
    {
        $line = __LINE__;
        $className = "\\Spiral\\Tests\\Scaffolder\\App\\Request\\Sample{$line}Request";
        $output = $this->console()->run('create:filter', [
            'name'     => 'sample' . $line,
            '--entity' => SourceEntity::class
        ]);

        $this->assertStringNotContainsString('Unable', $output->getOutput()->fetch());

        clearstatcache();
        $this->assertTrue(class_exists($className));

        $reflection = new ReflectionClass($className);

        try {
            $schema = $reflection->getConstant('SCHEMA');
            $this->assertSame('data:noTypeString', $schema['noTypeString']);
            $this->assertSame('data:obj', $schema['obj']);
            $this->assertSame('data:intFromPhpDoc', $schema['intFromPhpDoc']);
            $this->assertSame('data:noTypeWithFloatDefault', $schema['noTypeWithFloatDefault']);

            $validates = $reflection->getConstant('VALIDATES');
            $this->assertSame(['notEmpty', 'string'], $validates['noTypeString']);
            $this->assertSame(['notEmpty', 'string'], $validates['obj']);
            $this->assertSame(['notEmpty', 'integer'], $validates['intFromPhpDoc']);
            $this->assertSame(['notEmpty', 'float'], $validates['noTypeWithFloatDefault']);
        } finally {
            $this->deleteDeclaration($className);
        }
    }
}
