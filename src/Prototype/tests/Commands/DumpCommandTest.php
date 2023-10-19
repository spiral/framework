<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Commands;

use Spiral\Console\Console;
use Spiral\Files\FilesInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class DumpCommandTest extends AbstractCommandsTestCase
{
    public function testCommandRegistered(): void
    {
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('list', new ArrayInput([]), $out);

        $result = $out->fetch();

        $this->assertStringContainsString('prototype:dump', $result);
    }

    public function testDump(): void
    {
        $files = $this->createMock(FilesInterface::class);
        $files
            ->expects($this->once())
            ->method('write')
            ->with(static::callback(fn () => true), static::callback($this->validateTrait(...)));

        $this->app->getContainer()->bindSingleton(FilesInterface::class, $files, true);

        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:dump', new ArrayInput([]), $out);

        $result = $out->fetch();

        $this->assertStringContainsString('Updating PrototypeTrait DOCComment... complete', $result);
    }

    private function validateTrait(string $content): bool
    {
        $this->assertStringContainsString('namespace Spiral\Prototype\Traits;', $content);
        $this->assertStringContainsString('use Spiral\Prototype\PrototypeRegistry;', $content);
        $this->assertStringContainsString(
            'This DocComment is auto-generated, do not edit or commit this file to repository.',
            $content
        );
        $this->assertStringContainsString('@property \Spiral\Tests\Prototype\Fixtures\TestApp $app', $content);
        $this->assertStringContainsString('@property \Spiral\Tokenizer\ClassesInterface $classLocator', $content);
        $this->assertStringContainsString('@property \Spiral\Console\Console $console', $content);
        $this->assertStringContainsString('@property \Spiral\Broadcasting\BroadcastInterface $broadcast', $content);
        $this->assertStringContainsString('@property \Psr\Container\ContainerInterface $container', $content);
        $this->assertStringContainsString('@property \Spiral\Encrypter\EncrypterInterface $encrypter', $content);
        $this->assertStringContainsString('@property \Spiral\Boot\EnvironmentInterface $env', $content);
        $this->assertStringContainsString('@property \Spiral\Files\FilesInterface $files', $content);
        $this->assertStringContainsString('@property \Spiral\Security\GuardInterface $guard', $content);
        $this->assertStringContainsString('@property \Spiral\Http\Http $http', $content);
        $this->assertStringContainsString('@property \Spiral\Translator\TranslatorInterface $i18n', $content);
        $this->assertStringContainsString('@property \Spiral\Http\Request\InputManager $input', $content);
        $this->assertStringContainsString('@property \Spiral\Session\SessionScope $session', $content);
        $this->assertStringContainsString('@property \Spiral\Cookies\CookieManager $cookies', $content);
        $this->assertStringContainsString('@property \Psr\Log\LoggerInterface $logger', $content);
        $this->assertStringContainsString('@property \Spiral\Logger\LogsInterface $logs', $content);
        $this->assertStringContainsString('@property \Spiral\Boot\MemoryInterface $memory', $content);
        $this->assertStringContainsString(
            '@property \Spiral\Pagination\PaginationProviderInterface $paginators',
            $content
        );
        $this->assertStringContainsString('@property \Spiral\Queue\QueueInterface $queue', $content);
        $this->assertStringContainsString(
            '@property \Spiral\Queue\QueueConnectionProviderInterface $queueManager',
            $content
        );
        $this->assertStringContainsString('@property \Spiral\Http\Request\InputManager $request', $content);
        $this->assertStringContainsString('@property \Spiral\Http\ResponseWrapper $response', $content);
        $this->assertStringContainsString('@property \Spiral\Router\RouterInterface $router', $content);
        $this->assertStringContainsString('@property \Spiral\Snapshots\SnapshotterInterface $snapshots', $content);
        $this->assertStringContainsString('@property \Spiral\Storage\BucketInterface $storage', $content);
        $this->assertStringContainsString('@property \Spiral\Serializer\SerializerManager $serializer', $content);
        $this->assertStringContainsString('@property \Spiral\Validation\ValidationInterface $validator', $content);
        $this->assertStringContainsString('@property \Spiral\Views\ViewsInterface $views', $content);
        $this->assertStringContainsString('@property \Spiral\Auth\AuthScope $auth', $content);
        $this->assertStringContainsString('@property \Spiral\Auth\TokenStorageInterface $authTokens', $content);
        $this->assertStringContainsString('@property \Psr\SimpleCache\CacheInterface $cache', $content);
        $this->assertStringContainsString(
            '@property \Spiral\Cache\CacheStorageProviderInterface $cacheManager',
            $content
        );
        $this->assertStringContainsString(
            '@property \Spiral\Exceptions\ExceptionHandlerInterface $exceptionHandler',
            $content
        );
        $this->assertStringContainsString('trait PrototypeTrait', $content);
        $this->assertStringContainsString('public function __get(string $name): mixed', $content);
        $this->assertStringContainsString('return $container->get($target->type->name());', $content);

        return true;
    }
}
