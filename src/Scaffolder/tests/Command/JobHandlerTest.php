<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use ReflectionClass;
use ReflectionException;
use Throwable;

class JobHandlerTest extends AbstractCommandTestCase
{
    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffold(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Job\\SampleJob';

        $this->console()->run('create:jobHandler', [
            'name'      => 'sample',
            '--comment' => 'Sample Job Handler'
        ]);

        clearstatcache();
        $this->assertTrue(class_exists($class));

        $reflection = new ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString('strict_types=1', $content);
        $this->assertStringContainsString('{project-name}', $content);
        $this->assertStringContainsString('@author {author-name}', $content);
        $this->assertStringContainsString('Sample Job Handler', $reflection->getDocComment());
        $this->assertTrue($reflection->hasMethod('invoke'));
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffoldWithCustomNamespace(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Custom\\Job\\SampleJob';

        $this->console()->run('create:jobHandler', [
            'name' => 'sample',
            '--namespace' => 'Spiral\\Tests\\Scaffolder\\App\\Custom\\Job'
        ]);

        clearstatcache();
        $this->assertTrue(\class_exists($class));

        $reflection = new ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString(
            'App/Custom/Job/SampleJob.php',
            \str_replace('\\', '/', $reflection->getFileName())
        );
        $this->assertStringContainsString('App\Custom\Job', $content);
    }

    public function testShowInstructionAfterInstallation(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Job\\SampleJob';

        $result = $this->console()->run('create:jobHandler', [
            'name'      => 'sample',
            '--comment' => 'Sample Job Handler'
        ]);

        $output = $result->getOutput()->fetch();

        $this->assertSame(
            <<<OUTPUT
            Declaration of 'SampleJob' has been successfully written into 'Job/SampleJob.php'.

            Next steps:
            1. Read more about Job handlers in the documentation: https://spiral.dev/docs/queue-jobs

            OUTPUT,
            $output
        );
    }
}
