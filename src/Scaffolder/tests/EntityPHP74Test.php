<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder;

use ReflectionClass;
use Spiral\Tests\Scaffolder\Command\AbstractCommandTest;
use Spiral\Tests\Scaffolder\Fixtures\SourceEntity74;

/**
 * @requires PHP >= 7.4
 */
class EntityPHP74Test extends AbstractCommandTest
{
    /**
     * @throws \Throwable
     */
    public function testFromEntity(): void
    {
        $line = __LINE__;
        $className = __NAMESPACE__ . "\\App\\Request\\Sample{$line}Request";
        $output = $this->console()->run('create:filter', [
            'name'     => 'sample' . $line,
            '--entity' => SourceEntity74::class
        ]);

        $this->assertStringNotContainsString('Unable', $output->getOutput()->fetch());

        clearstatcache();
        $this->assertTrue(class_exists($className));

        $reflection = new ReflectionClass($className);

        try {
            $schema = $reflection->getConstant('SCHEMA');
            $this->assertSame('data:typedBool', $schema['typedBool']);
            $this->assertSame('data:noTypeString', $schema['noTypeString']);
            $this->assertSame('data:obj', $schema['obj']);
            $this->assertSame('data:intFromPhpDoc', $schema['intFromPhpDoc']);
            $this->assertSame('data:noTypeWithFloatDefault', $schema['noTypeWithFloatDefault']);

            $validates = $reflection->getConstant('VALIDATES');
            $this->assertSame(['notEmpty', 'boolean'], $validates['typedBool']);
            $this->assertSame(['notEmpty', 'string'], $validates['noTypeString']);
            $this->assertSame(['notEmpty', 'string'], $validates['obj']);
            $this->assertSame(['notEmpty', 'integer'], $validates['intFromPhpDoc']);
            $this->assertSame(['notEmpty', 'float'], $validates['noTypeWithFloatDefault']);
        } finally {
            $this->deleteDeclaration($className);
        }
    }
}
