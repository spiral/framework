<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Commands;

use Spiral\Console\Console;
use Spiral\Tests\Prototype\Fixtures\TestApp;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class ListCommandTest extends AbstractCommandsTestCase
{
    public function testCommandRegistered(): void
    {
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('list', new ArrayInput([]), $out);

        $result = $out->fetch();

        self::assertStringContainsString('prototype:list', $result);
    }

    public function testList(): void
    {
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:list', new ArrayInput([]), $out);

        $result = $out->fetch();

        self::assertStringContainsString('app', $result);
        self::assertStringContainsString(TestApp::class, $result);
        self::assertStringContainsString('classLocator', $result);
        self::assertStringContainsString(\Spiral\Tokenizer\ClassesInterface::class, $result);
        self::assertStringContainsString('console', $result);
        self::assertStringContainsString(\Spiral\Console\Console::class, $result);
        self::assertStringContainsString('broadcast', $result);
        self::assertStringContainsString(\Spiral\Broadcasting\BroadcastInterface::class, $result);
        self::assertStringContainsString('container', $result);
        self::assertStringContainsString(\Psr\Container\ContainerInterface::class, $result);
        self::assertStringContainsString('encrypter', $result);
        self::assertStringContainsString(\Spiral\Encrypter\EncrypterInterface::class, $result);
        self::assertStringContainsString('env', $result);
        self::assertStringContainsString(\Spiral\Boot\EnvironmentInterface::class, $result);
        self::assertStringContainsString('files', $result);
        self::assertStringContainsString(\Spiral\Files\FilesInterface::class, $result);
        self::assertStringContainsString('guard', $result);
        self::assertStringContainsString(\Spiral\Security\GuardInterface::class, $result);
        self::assertStringContainsString('http', $result);
        self::assertStringContainsString(\Spiral\Http\Http::class, $result);
        self::assertStringContainsString('i18n', $result);
        self::assertStringContainsString(\Spiral\Translator\TranslatorInterface::class, $result);
        self::assertStringContainsString('input', $result);
        self::assertStringContainsString(\Spiral\Http\Request\InputManager::class, $result);
        self::assertStringContainsString('session', $result);
        self::assertStringContainsString(\Spiral\Session\SessionScope::class, $result);
        self::assertStringContainsString('cookies', $result);
        self::assertStringContainsString(\Spiral\Cookies\CookieManager::class, $result);
        self::assertStringContainsString('logger', $result);
        self::assertStringContainsString(\Psr\Log\LoggerInterface::class, $result);
        self::assertStringContainsString('logs', $result);
        self::assertStringContainsString(\Spiral\Logger\LogsInterface::class, $result);
        self::assertStringContainsString('memory', $result);
        self::assertStringContainsString(\Spiral\Boot\MemoryInterface::class, $result);
        self::assertStringContainsString('paginators', $result);
        self::assertStringContainsString(\Spiral\Pagination\PaginationProviderInterface::class, $result);
        self::assertStringContainsString('queue', $result);
        self::assertStringContainsString(\Spiral\Queue\QueueInterface::class, $result);
        self::assertStringContainsString('queueManager', $result);
        self::assertStringContainsString(\Spiral\Queue\QueueConnectionProviderInterface::class, $result);
        self::assertStringContainsString('request', $result);
        self::assertStringContainsString(\Spiral\Http\Request\InputManager::class, $result);
        self::assertStringContainsString('response', $result);
        self::assertStringContainsString(\Spiral\Http\ResponseWrapper::class, $result);
        self::assertStringContainsString('router', $result);
        self::assertStringContainsString(\Spiral\Router\RouterInterface::class, $result);
        self::assertStringContainsString('snapshots', $result);
        self::assertStringContainsString(\Spiral\Snapshots\SnapshotterInterface::class, $result);
        self::assertStringContainsString('storage', $result);
        self::assertStringContainsString(\Spiral\Storage\BucketInterface::class, $result);
        self::assertStringContainsString('serializer', $result);
        self::assertStringContainsString(\Spiral\Serializer\SerializerManager::class, $result);
        self::assertStringContainsString('validator', $result);
        self::assertStringContainsString(\Spiral\Validation\ValidationInterface::class, $result);
        self::assertStringContainsString('views', $result);
        self::assertStringContainsString(\Spiral\Views\ViewsInterface::class, $result);
        self::assertStringContainsString('auth', $result);
        self::assertStringContainsString(\Spiral\Auth\AuthScope::class, $result);
        self::assertStringContainsString('authTokens', $result);
        self::assertStringContainsString(\Spiral\Auth\TokenStorageInterface::class, $result);
        self::assertStringContainsString('cache', $result);
        self::assertStringContainsString(\Psr\SimpleCache\CacheInterface::class, $result);
        self::assertStringContainsString('cacheManager', $result);
        self::assertStringContainsString(\Spiral\Cache\CacheStorageProviderInterface::class, $result);
        self::assertStringContainsString('exceptionHandler', $result);
        self::assertStringContainsString(\Spiral\Exceptions\ExceptionHandlerInterface::class, $result);
    }
}
