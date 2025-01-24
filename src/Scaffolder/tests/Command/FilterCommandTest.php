<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\Command;

use Spiral\Filters\Model\Filter;

final class FilterCommandTest extends AbstractCommandTestCase
{
    public function testScaffold(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Filter\\SampleFilter';

        $this->console()->run('create:filter', [
            'name' => 'sample',
            '--comment' => 'Sample Filter',
        ]);

        \clearstatcache();
        self::assertTrue(\class_exists($class));

        $reflection = new \ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        self::assertStringContainsString('strict_types=1', $content);
        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isSubclassOf(Filter::class));
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testScaffoldWithCustomNamespace(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Custom\\Filter\\SampleFilter';

        $this->console()->run('create:filter', [
            'name' => 'sample',
            '--namespace' => 'Spiral\\Tests\\Scaffolder\\App\\Custom\\Filter',
        ]);

        \clearstatcache();
        self::assertTrue(\class_exists($class));

        $reflection = new \ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        self::assertStringContainsString('App/Custom/Filter/SampleFilter.php', \str_replace('\\', '/', $reflection->getFileName()));
        self::assertStringContainsString('App\Custom\Filter', $content);
    }

    public function testCreateProperty(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Filter\\SampleWithPropertyFilter';
        $this->console()->run('create:filter', [
            'name' => 'SampleWithProperty',
            '--property' => [
                'name:post',
                'age:query:int',
                'tags:data:array',
                'ipAddress:ip',
                'avatar:file',
                'path:uri',
                'token:token',
            ],
        ]);

        \clearstatcache();
        self::assertTrue(\class_exists($class));

        $reflection = new \ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        self::assertStringContainsString('use Spiral\Filters\Attribute\Input\Post;', $content);
        self::assertStringContainsString('#[Post(key: \'name\')]', $content);
        self::assertStringContainsString('public string $name;', $content);

        self::assertStringContainsString('use Spiral\Filters\Attribute\Input\Query;', $content);
        self::assertStringContainsString('#[Query(key: \'age\')]', $content);
        self::assertStringContainsString('public int $age;', $content);

        self::assertStringContainsString('use Spiral\Filters\Attribute\Input\Data;', $content);
        self::assertStringContainsString('#[Data(key: \'tags\')]', $content);
        self::assertStringContainsString('public array $tags;', $content);

        self::assertStringContainsString('use Spiral\Filters\Attribute\Input\RemoteAddress;', $content);
        self::assertStringContainsString('#[RemoteAddress(key: \'ipAddress\')]', $content);
        self::assertStringContainsString('public string $ipAddress;', $content);

        self::assertStringContainsString('use Spiral\Filters\Attribute\Input\File;', $content);
        self::assertStringContainsString('#[File(key: \'avatar\')]', $content);
        self::assertStringContainsString('public \Psr\Http\Message\UploadedFileInterface $avatar;', $content);

        self::assertStringContainsString('use Spiral\Filters\Attribute\Input\Uri;', $content);
        self::assertStringContainsString('#[Uri(key: \'path\')]', $content);
        self::assertStringContainsString('public \Psr\Http\Message\UriInterface $path;', $content);

        self::assertStringContainsString('use Spiral\Filters\Attribute\Input\BearerToken;', $content);
        self::assertStringContainsString('#[BearerToken(key: \'token\')]', $content);
        self::assertStringContainsString('public string $token;', $content);
    }

    public function testShowInstructionAfterInstallation(): void
    {
        $this->className = $class = '\\Spiral\\Tests\\Scaffolder\\App\\Filter\\SampleFilter';

        $result = $this->console()->run('create:filter', [
            'name' => 'sample',
            '--comment' => 'Sample Filter',
        ]);

        $output = $result->getOutput()->fetch();

        self::assertStringEqualsStringIgnoringLineEndings(<<<OUTPUT
            Declaration of 'SampleFilter' has been successfully written into 'Filter/SampleFilter.php'.

            Next steps:
            1. Read more about Filter Objects in the documentation: https://spiral.dev/docs/filters-filter
            2. Read more about Filter validation handling here: https://spiral.dev/docs/filters-filter#handle-validation-errors

            OUTPUT, $output);
    }
}
