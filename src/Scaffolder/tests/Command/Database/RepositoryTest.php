<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command\Database;

use ReflectionClass;
use ReflectionException;
use Spiral\Tests\Scaffolder\Command\AbstractCommandTest;
use Throwable;

class RepositoryTest extends AbstractCommandTest
{
    private const CLASS_NAME = '\\Spiral\\Tests\\Scaffolder\\App\\Repository\\AnotherSampleRepository';

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
        $this->console()->run('create:repository', [
            'name'      => 'anotherSample',
            '--comment' => 'Sample Repository'
        ]);

        clearstatcache();
        $this->assertTrue(class_exists(self::CLASS_NAME));

        $reflection = new ReflectionClass(self::CLASS_NAME);

        $this->assertStringContainsString('strict_types=1', $this->files()->read($reflection->getFileName()));
        $this->assertStringContainsString('Sample Repository', $reflection->getDocComment());
    }
}
