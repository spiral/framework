<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

class JobHandlerTest extends AbstractCommandTestCase
{
    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testScaffold(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Job\\SampleJob';

        $this->console()->run('create:jobHandler', [
            'name'      => 'sample',
            '--comment' => 'Sample Job Handler',
        ]);

        clearstatcache();
        self::assertTrue(class_exists($class));

        $reflection = new \ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        self::assertStringContainsString('strict_types=1', $content);
        self::assertStringContainsString('{project-name}', $content);
        self::assertStringContainsString('@author {author-name}', $content);
        self::assertStringContainsString('function invoke(string $id, mixed $payload, array $headers)', $content);
        self::assertStringContainsString('Sample Job Handler', $reflection->getDocComment());
        self::assertTrue($reflection->hasMethod('invoke'));
    }

    /**
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testScaffoldWithCustomNamespace(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Custom\\Job\\SampleJob';

        $this->console()->run('create:jobHandler', [
            'name' => 'sample',
            '--namespace' => 'Spiral\\Tests\\Scaffolder\\App\\Custom\\Job',
        ]);

        clearstatcache();
        self::assertTrue(\class_exists($class));

        $reflection = new \ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        self::assertStringContainsString('App/Custom/Job/SampleJob.php', \str_replace('\\', '/', $reflection->getFileName()));
        self::assertStringContainsString('App\Custom\Job', $content);
    }

    public function testShowInstructionAfterInstallation(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Job\\SampleJob';

        $result = $this->console()->run('create:jobHandler', [
            'name'      => 'sample',
            '--comment' => 'Sample Job Handler',
        ]);

        $output = $result->getOutput()->fetch();

        self::assertStringEqualsStringIgnoringLineEndings(<<<OUTPUT
            Declaration of 'SampleJob' has been successfully written into 'Job/SampleJob.php'.

            Next steps:
            1. Read more about Job handlers in the documentation: https://spiral.dev/docs/queue-jobs

            OUTPUT, $output);
    }
}
