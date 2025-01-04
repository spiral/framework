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

        self::assertStringContainsString('prototype:dump', $result);
    }

    public function testDump(): void
    {
        $files = $this->createMock(FilesInterface::class);
        $files
            ->expects($this->once())
            ->method('write')
            ->with(static::callback(fn (): bool => true), static::callback($this->validateTrait(...)));

        $this->app->getContainer()->bindSingleton(FilesInterface::class, $files, true);

        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:dump', new ArrayInput([]), $out);

        $result = $out->fetch();

        self::assertStringContainsString('Updating PrototypeTrait DOCComment... complete', $result);
    }

    private function validateTrait(string $content): bool
    {
        self::assertStringContainsString('namespace Spiral\Prototype\Traits;', $content);
        self::assertStringContainsString('use Spiral\Prototype\PrototypeRegistry;', $content);
        self::assertStringContainsString('This DocComment is auto-generated, do not edit or commit this file to repository.', $content);
        self::assertStringContainsString('@property \Spiral\Tests\Prototype\Fixtures\TestApp $app', $content);
        self::assertStringContainsString('@property \Spiral\Tokenizer\ClassesInterface $classLocator', $content);
        self::assertStringContainsString('@property \Spiral\Console\Console $console', $content);
        self::assertStringContainsString('@property \Spiral\Broadcasting\BroadcastInterface $broadcast', $content);
        self::assertStringContainsString('@property \Psr\Container\ContainerInterface $container', $content);
        self::assertStringContainsString('@property \Spiral\Encrypter\EncrypterInterface $encrypter', $content);
        self::assertStringContainsString('@property \Spiral\Boot\EnvironmentInterface $env', $content);
        self::assertStringContainsString('@property \Spiral\Files\FilesInterface $files', $content);
        self::assertStringContainsString('@property \Spiral\Security\GuardInterface $guard', $content);
        self::assertStringContainsString('@property \Spiral\Http\Http $http', $content);
        self::assertStringContainsString('@property \Spiral\Translator\TranslatorInterface $i18n', $content);
        self::assertStringContainsString('@property \Spiral\Http\Request\InputManager $input', $content);
        self::assertStringContainsString('@property \Spiral\Session\SessionScope $session', $content);
        self::assertStringContainsString('@property \Spiral\Cookies\CookieManager $cookies', $content);
        self::assertStringContainsString('@property \Psr\Log\LoggerInterface $logger', $content);
        self::assertStringContainsString('@property \Spiral\Logger\LogsInterface $logs', $content);
        self::assertStringContainsString('@property \Spiral\Boot\MemoryInterface $memory', $content);
        self::assertStringContainsString('@property \Spiral\Pagination\PaginationProviderInterface $paginators', $content);
        self::assertStringContainsString('@property \Spiral\Queue\QueueInterface $queue', $content);
        self::assertStringContainsString('@property \Spiral\Queue\QueueConnectionProviderInterface $queueManager', $content);
        self::assertStringContainsString('@property \Spiral\Http\Request\InputManager $request', $content);
        self::assertStringContainsString('@property \Spiral\Http\ResponseWrapper $response', $content);
        self::assertStringContainsString('@property \Spiral\Router\RouterInterface $router', $content);
        self::assertStringContainsString('@property \Spiral\Snapshots\SnapshotterInterface $snapshots', $content);
        self::assertStringContainsString('@property \Spiral\Storage\BucketInterface $storage', $content);
        self::assertStringContainsString('@property \Spiral\Serializer\SerializerManager $serializer', $content);
        self::assertStringContainsString('@property \Spiral\Validation\ValidationInterface $validator', $content);
        self::assertStringContainsString('@property \Spiral\Views\ViewsInterface $views', $content);
        self::assertStringContainsString('@property \Spiral\Auth\AuthScope $auth', $content);
        self::assertStringContainsString('@property \Spiral\Auth\TokenStorageInterface $authTokens', $content);
        self::assertStringContainsString('@property \Psr\SimpleCache\CacheInterface $cache', $content);
        self::assertStringContainsString('@property \Spiral\Cache\CacheStorageProviderInterface $cacheManager', $content);
        self::assertStringContainsString('@property \Spiral\Exceptions\ExceptionHandlerInterface $exceptionHandler', $content);
        self::assertStringContainsString('trait PrototypeTrait', $content);
        self::assertStringContainsString('public function __get(string $name): mixed', $content);
        self::assertStringContainsString('return $container->get($target->type->name());', $content);

        return true;
    }
}
