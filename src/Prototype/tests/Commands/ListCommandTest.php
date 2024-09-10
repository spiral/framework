<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Commands;

use Spiral\Tokenizer\ClassesInterface;
use Spiral\Broadcasting\BroadcastInterface;
use Psr\Container\ContainerInterface;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Files\FilesInterface;
use Spiral\Security\GuardInterface;
use Spiral\Http\Http;
use Spiral\Translator\TranslatorInterface;
use Spiral\Http\Request\InputManager;
use Spiral\Session\SessionScope;
use Spiral\Cookies\CookieManager;
use Psr\Log\LoggerInterface;
use Spiral\Logger\LogsInterface;
use Spiral\Boot\MemoryInterface;
use Spiral\Pagination\PaginationProviderInterface;
use Spiral\Queue\QueueInterface;
use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\RouterInterface;
use Spiral\Snapshots\SnapshotterInterface;
use Spiral\Storage\BucketInterface;
use Spiral\Serializer\SerializerManager;
use Spiral\Validation\ValidationInterface;
use Spiral\Views\ViewsInterface;
use Spiral\Auth\AuthScope;
use Spiral\Auth\TokenStorageInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Cache\CacheStorageProviderInterface;
use Spiral\Exceptions\ExceptionHandlerInterface;
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

        $this->assertStringContainsString('prototype:list', $result);
    }

    public function testList(): void
    {
        $out = new BufferedOutput();
        $this->app->get(Console::class)->run('prototype:list', new ArrayInput([]), $out);

        $result = $out->fetch();

        $this->assertStringContainsString('app', $result);
        $this->assertStringContainsString(TestApp::class, $result);
        $this->assertStringContainsString('classLocator', $result);
        $this->assertStringContainsString(ClassesInterface::class, $result);
        $this->assertStringContainsString('console', $result);
        $this->assertStringContainsString(Console::class, $result);
        $this->assertStringContainsString('broadcast', $result);
        $this->assertStringContainsString(BroadcastInterface::class, $result);
        $this->assertStringContainsString('container', $result);
        $this->assertStringContainsString(ContainerInterface::class, $result);
        $this->assertStringContainsString('encrypter', $result);
        $this->assertStringContainsString(EncrypterInterface::class, $result);
        $this->assertStringContainsString('env', $result);
        $this->assertStringContainsString(EnvironmentInterface::class, $result);
        $this->assertStringContainsString('files', $result);
        $this->assertStringContainsString(FilesInterface::class, $result);
        $this->assertStringContainsString('guard', $result);
        $this->assertStringContainsString(GuardInterface::class, $result);
        $this->assertStringContainsString('http', $result);
        $this->assertStringContainsString(Http::class, $result);
        $this->assertStringContainsString('i18n', $result);
        $this->assertStringContainsString(TranslatorInterface::class, $result);
        $this->assertStringContainsString('input', $result);
        $this->assertStringContainsString(InputManager::class, $result);
        $this->assertStringContainsString('session', $result);
        $this->assertStringContainsString(SessionScope::class, $result);
        $this->assertStringContainsString('cookies', $result);
        $this->assertStringContainsString(CookieManager::class, $result);
        $this->assertStringContainsString('logger', $result);
        $this->assertStringContainsString(LoggerInterface::class, $result);
        $this->assertStringContainsString('logs', $result);
        $this->assertStringContainsString(LogsInterface::class, $result);
        $this->assertStringContainsString('memory', $result);
        $this->assertStringContainsString(MemoryInterface::class, $result);
        $this->assertStringContainsString('paginators', $result);
        $this->assertStringContainsString(PaginationProviderInterface::class, $result);
        $this->assertStringContainsString('queue', $result);
        $this->assertStringContainsString(QueueInterface::class, $result);
        $this->assertStringContainsString('queueManager', $result);
        $this->assertStringContainsString(QueueConnectionProviderInterface::class, $result);
        $this->assertStringContainsString('request', $result);
        $this->assertStringContainsString(InputManager::class, $result);
        $this->assertStringContainsString('response', $result);
        $this->assertStringContainsString(ResponseWrapper::class, $result);
        $this->assertStringContainsString('router', $result);
        $this->assertStringContainsString(RouterInterface::class, $result);
        $this->assertStringContainsString('snapshots', $result);
        $this->assertStringContainsString(SnapshotterInterface::class, $result);
        $this->assertStringContainsString('storage', $result);
        $this->assertStringContainsString(BucketInterface::class, $result);
        $this->assertStringContainsString('serializer', $result);
        $this->assertStringContainsString(SerializerManager::class, $result);
        $this->assertStringContainsString('validator', $result);
        $this->assertStringContainsString(ValidationInterface::class, $result);
        $this->assertStringContainsString('views', $result);
        $this->assertStringContainsString(ViewsInterface::class, $result);
        $this->assertStringContainsString('auth', $result);
        $this->assertStringContainsString(AuthScope::class, $result);
        $this->assertStringContainsString('authTokens', $result);
        $this->assertStringContainsString(TokenStorageInterface::class, $result);
        $this->assertStringContainsString('cache', $result);
        $this->assertStringContainsString(CacheInterface::class, $result);
        $this->assertStringContainsString('cacheManager', $result);
        $this->assertStringContainsString(CacheStorageProviderInterface::class, $result);
        $this->assertStringContainsString('exceptionHandler', $result);
        $this->assertStringContainsString(ExceptionHandlerInterface::class, $result);
    }
}
