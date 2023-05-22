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

        clearstatcache();
        $this->assertTrue(\class_exists($class));

        $reflection = new \ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString('strict_types=1', $content);
        $this->assertTrue($reflection->isFinal());
        $this->assertTrue($reflection->isSubclassOf(Filter::class));
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

        clearstatcache();
        $this->assertTrue(\class_exists($class));

        $reflection = new \ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString(
            'App/Custom/Filter/SampleFilter.php',
            \str_replace('\\', '/', $reflection->getFileName()),
        );
        $this->assertStringContainsString('App\Custom\Filter', $content);
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

        clearstatcache();
        $this->assertTrue(\class_exists($class));

        $reflection = new \ReflectionClass($class);
        $content = $this->files()->read($reflection->getFileName());

        $this->assertStringContainsString('use Spiral\Filters\Attribute\Input\Post;', $content);
        $this->assertStringContainsString('#[Post(key: \'name\')]', $content);
        $this->assertStringContainsString('public string $name;', $content);

        $this->assertStringContainsString('use Spiral\Filters\Attribute\Input\Query;', $content);
        $this->assertStringContainsString('#[Query(key: \'age\')]', $content);
        $this->assertStringContainsString('public int $age;', $content);

        $this->assertStringContainsString('use Spiral\Filters\Attribute\Input\Data;', $content);
        $this->assertStringContainsString('#[Data(key: \'tags\')]', $content);
        $this->assertStringContainsString('public array $tags;', $content);

        $this->assertStringContainsString('use Spiral\Filters\Attribute\Input\RemoteAddress;', $content);
        $this->assertStringContainsString('#[RemoteAddress(key: \'ipAddress\')]', $content);
        $this->assertStringContainsString('public string $ipAddress;', $content);

        $this->assertStringContainsString('use Spiral\Filters\Attribute\Input\File;', $content);
        $this->assertStringContainsString('#[File(key: \'avatar\')]', $content);
        $this->assertStringContainsString('public \Psr\Http\Message\UploadedFileInterface $avatar;', $content);

        $this->assertStringContainsString('use Spiral\Filters\Attribute\Input\Uri;', $content);
        $this->assertStringContainsString('#[Uri(key: \'path\')]', $content);
        $this->assertStringContainsString('public \Psr\Http\Message\UriInterface $path;', $content);

        $this->assertStringContainsString('use Spiral\Filters\Attribute\Input\BearerToken;', $content);
        $this->assertStringContainsString('#[BearerToken(key: \'token\')]', $content);
        $this->assertStringContainsString('public string $token;', $content);
    }
}
