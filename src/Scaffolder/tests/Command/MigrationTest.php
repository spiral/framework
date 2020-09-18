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
use Spiral\Scaffolder\Exception\ScaffolderException;
use Throwable;

class MigrationTest extends AbstractCommandTest
{
    private const CLASS_NAME  = '\\Spiral\\Tests\\Scaffolder\\App\\SampleMigration';
    private const CLASS_NAME2 = '\\Spiral\\Tests\\Scaffolder\\App\\Sample2Migration';

    /**
     * @throws Throwable
     */
    public function tearDown(): void
    {
        $this->files()->deleteDirectory($this->app->directory('app') . 'migrations', true);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffoldWithTable(): void
    {
        $this->console()->run('create:migration', $this->input('sample', true));

        clearstatcache();

        foreach ($this->files()->getFiles($this->app->directory('app') . 'migrations') as $file) {
            require_once $file;
        }

        $source = $this->doGeneralAssertions(self::CLASS_NAME);

        $this->assertStringContainsString('sample_table', $source);
        $this->assertStringContainsString('id', $source);
        $this->assertStringContainsString('primary', $source);
        $this->assertStringContainsString('content', $source);
        $this->assertStringContainsString('text', $source);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffoldWithoutTable(): void
    {
        $this->console()->run('create:migration', $this->input('sample2', false));

        clearstatcache();

        foreach ($this->files()->getFiles($this->app->directory('app') . 'migrations') as $file) {
            require_once $file;
        }

        $source = $this->doGeneralAssertions(self::CLASS_NAME2);

        $this->assertStringNotContainsString('sample_table', $source);
    }

    /**
     * @throws Throwable
     */
    public function testScaffoldException(): void
    {
        $this->expectException(ScaffolderException::class);

        $this->console()->run('create:migration', [
            'name'    => 'sample3',
            '--table' => 'sample3_table',
            '--field' => ['id',]
        ]);
    }

    private function input(string $name, bool $withTable): array
    {
        $input = [
            'name' => $name,
            '-f'   => [
                'id:primary',
                'content:text'
            ]
        ];

        if ($withTable) {
            $input['--table'] = 'sample_table';
        }

        return $input;
    }

    /**
     * @param string $className
     * @return string
     * @throws ReflectionException
     * @throws Throwable
     */
    private function doGeneralAssertions(string $className): string
    {
        clearstatcache();

        foreach ($this->files()->getFiles($this->app->directory('app') . 'migrations') as $file) {
            require_once $file;
        }

        $this->assertTrue(class_exists($className));

        $reflection = new ReflectionClass($className);

        $this->assertStringContainsString('strict_types=1', $this->files()->read($reflection->getFileName()));
        $this->assertTrue($reflection->hasMethod('up'));
        $this->assertTrue($reflection->hasMethod('down'));

        return file_get_contents($reflection->getFileName());
    }
}
