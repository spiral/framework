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

use Exception;
use Reflection;
use ReflectionClass;
use ReflectionException;
use Spiral\Scaffolder\Exception\ScaffolderException;
use Spiral\Tests\Scaffolder\Command\AbstractCommandTest;
use Throwable;

use function Spiral\Scaffolder\trimPostfix;

class EntityTest extends AbstractCommandTest
{
    private const CLASS_NAME            = '\\Spiral\\Tests\\Scaffolder\\App\\Database\\Sample';
    private const REPOSITORY_CLASS_NAME = '\\Spiral\\Tests\\Scaffolder\\App\\Repository\\SampleRepository';

    /**
     * @throws Throwable
     */
    public function testScaffold(): void
    {
        $line = __LINE__;
        $className = self::CLASS_NAME . $line;
        $this->console()->run('create:entity', [
            'name'    => 'sample' . $line,
            '--field' => [
                'id:primary',
                'value:int'
            ],
        ]);

        clearstatcache();
        $this->assertTrue(class_exists($className));

        $reflection = new ReflectionClass($className);

        $this->assertTrue($reflection->hasProperty('id'));
        $this->assertTrue($reflection->hasProperty('value'));
        $this->assertStringContainsString('strict_types=1', $this->files()->read($reflection->getFileName()));

        $this->deleteDeclaration($className);
    }

    /**
     * @throws Throwable
     */
    public function testScaffoldExceptionOnField(): void
    {
        $this->expectException(ScaffolderException::class);

        $this->console()->run('create:entity', [
            'name'    => 'sample3',
            '--field' => ['id'],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testScaffoldExceptionOnAccessibility(): void
    {
        $this->expectException(ScaffolderException::class);

        $this->console()->run('create:entity', [
            'name'            => 'sample3',
            '--field'         => ['id:primary'],
            '--accessibility' => 'unknown'
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testScaffoldExceptionOnInflection(): void
    {
        $this->expectException(ScaffolderException::class);

        $this->console()->run('create:entity', [
            'name'         => 'sample3',
            '--field'      => ['id:primary'],
            '--inflection' => 'unknown'
        ]);
    }

    /**
     * @dataProvider accessibilityDataProvider
     * @param int         $line
     * @param string|null $accessibility
     * @param string      $modifier
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testAccessibility(int $line, ?string $accessibility, string $modifier): void
    {
        $className = self::CLASS_NAME . $line;
        $input = [
            'name'    => 'sample' . $line,
            '--field' => [
                'id:primary',
            ],
        ];
        if (!empty($accessibility)) {
            $input['--accessibility'] = $accessibility;
        }

        $this->console()->run('create:entity', $input);

        clearstatcache();
        $this->assertTrue(class_exists($className));

        $reflection = new ReflectionClass($className);

        $this->assertTrue($reflection->hasProperty('id'));

        $property = $reflection->getProperty('id');
        $modifiers = Reflection::getModifierNames($property->getModifiers());

        $this->assertContains($modifier, $modifiers);

        $this->deleteDeclaration($className);
    }

    public function accessibilityDataProvider(): array
    {
        return [
            [__LINE__, null, 'public'],
            [__LINE__, 'public', 'public'],
            [__LINE__, 'protected', 'protected'],
            [__LINE__, 'private', 'private'],
        ];
    }

    /**
     * @dataProvider accessorsDataProvider
     * @param int         $line
     * @param string|null $accessibility
     * @param bool        $hasAccessors
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testAccessors(int $line, ?string $accessibility, bool $hasAccessors): void
    {
        $className = self::CLASS_NAME . $line;
        $input = [
            'name'    => 'sample' . $line,
            '--field' => [
                'id:primary',
                'name:string',
            ],
        ];
        if (!empty($accessibility)) {
            $input['--accessibility'] = $accessibility;
        }

        $this->console()->run('create:entity', $input);

        clearstatcache();
        $this->assertTrue(class_exists($className));

        $reflection = new ReflectionClass($className);

        $methods = [];
        foreach ($reflection->getMethods() as $method) {
            $methods[] = $method->getName();
        }

        if ($hasAccessors) {
            $this->assertContains('getId', $methods);
            $this->assertContains('getName', $methods);
            $this->assertContains('setId', $methods);
            $this->assertContains('setName', $methods);
        } else {
            $this->assertNotContains('getId', $methods);
            $this->assertNotContains('setId', $methods);
            $this->assertNotContains('setId', $methods);
            $this->assertNotContains('setName', $methods);
        }

        $this->deleteDeclaration($className);
    }

    public function accessorsDataProvider(): array
    {
        return [
            [__LINE__, null, false],
            [__LINE__, 'public', false],
            [__LINE__, 'protected', true],
            [__LINE__, 'private', true],
        ];
    }

    /**
     * @dataProvider repositoryDataProvider
     * @param string $className
     * @param string $name
     * @param string $repositoryClassName
     * @param string $repositoryName
     * @param bool   $exists
     * @throws Throwable
     */
    public function testRepository(
        string $className,
        string $name,
        string $repositoryClassName,
        string $repositoryName,
        bool $exists
    ): void {
        $this->console()->run('create:entity', [
            'name'         => $name,
            '--repository' => $repositoryName
        ]);

        clearstatcache();
        $this->assertEquals($exists, class_exists($repositoryClassName));

        if ($exists) {
            $reflection = new ReflectionClass($className);
            $source = $this->files()->read($reflection->getFileName());

            $this->assertStringContainsString("repository=\"$repositoryClassName\"", $source);
        }

        $this->deleteDeclaration($className);
        $this->deleteDeclaration($repositoryClassName);
    }

    public function repositoryDataProvider(): array
    {
        $line1 = __LINE__;
        $line2 = __LINE__;
        $line3 = __LINE__;

        $repositoryClassName1 = trimPostfix(self::REPOSITORY_CLASS_NAME, 'repository') . $line1 . 'Repository';
        $repositoryClassName2 = trimPostfix(self::REPOSITORY_CLASS_NAME, 'repository') . $line2 . 'Repository';
        $repositoryClassName3 = trimPostfix(self::REPOSITORY_CLASS_NAME, 'repository') . $line3 . 'Repository';

        return [
            [self::CLASS_NAME . $line1, 'sample' . $line1, $repositoryClassName1, 'sample', true],
            [self::CLASS_NAME . $line2, 'sample' . $line2, $repositoryClassName2, 'repository', true],
            [self::CLASS_NAME . $line3, 'sample' . $line3, $repositoryClassName3, '', false],
        ];
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testAnnotated(): void
    {
        $line = __LINE__;
        $className = self::CLASS_NAME . $line;
        $this->console()->run('create:entity', [
            'name'       => 'sample' . $line,
            '--field'    => [
                'id:primary',
                'myValue:?int'
            ],
            '--role'     => 'myRole',
            '--mapper'   => 'myMapper',
            '--table'    => 'myTable',
            '--database' => 'myDatabase'
        ]);

        clearstatcache();
        $this->assertTrue(class_exists($className));

        $reflection = new ReflectionClass($className);
        $source = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString('Entity', $source);
        $this->assertStringContainsString('myRole', $source);
        $this->assertStringContainsString('myMapper', $source);
        $this->assertStringContainsString('myTable', $source);
        $this->assertStringContainsString('myDatabase', $source);
        $this->assertStringContainsString('nullable', $source);

        $this->deleteDeclaration($className);
    }

    /**
     * @dataProvider inflectionDataProvider
     * @param int    $line
     * @param string $inflection
     * @param string $needle
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testAnnotatedInflection(int $line, string $inflection, string $needle): void
    {
        $className = self::CLASS_NAME . $line;
        $this->console()->run('create:entity', [
            'name'         => 'sample' . $line,
            '--field'      => [
                'id:primary',
                'myValue:int',
                'my_another_value:int'
            ],
            '--inflection' => $inflection,
        ]);

        clearstatcache();
        $this->assertTrue(class_exists($className));

        $reflection = new ReflectionClass($className);
        $source = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString($needle, $source);
        $this->assertStringContainsString('Entity', $source);

        $this->deleteDeclaration($className);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function inflectionDataProvider(): array
    {
        return [
            [__LINE__ + random_int(101, 200), 'tableize', 'my_value'],
            [__LINE__ + random_int(101, 200), 't', 'my_value'],
            [__LINE__ + random_int(201, 300), 'camelize', 'myAnotherValue'],
            [__LINE__ + random_int(201, 300), 'c', 'myAnotherValue'],
        ];
    }
}
