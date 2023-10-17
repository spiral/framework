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
        $this->assertStringContainsString(\Spiral\Tokenizer\ClassesInterface::class, $result);
        $this->assertStringContainsString('console', $result);
        $this->assertStringContainsString(\Spiral\Console\Console::class, $result);
        $this->assertStringContainsString('broadcast', $result);
        $this->assertStringContainsString(\Spiral\Broadcasting\BroadcastInterface::class, $result);
        $this->assertStringContainsString('container', $result);
        $this->assertStringContainsString(\Psr\Container\ContainerInterface::class, $result);
        $this->assertStringContainsString('encrypter', $result);
        $this->assertStringContainsString(\Spiral\Encrypter\EncrypterInterface::class, $result);
        $this->assertStringContainsString('env', $result);
        $this->assertStringContainsString(\Spiral\Boot\EnvironmentInterface::class, $result);
        $this->assertStringContainsString('files', $result);
        $this->assertStringContainsString(\Spiral\Files\FilesInterface::class, $result);
        $this->assertStringContainsString('guard', $result);
        $this->assertStringContainsString(\Spiral\Security\GuardInterface::class, $result);
        $this->assertStringContainsString('http', $result);
        $this->assertStringContainsString(\Spiral\Http\Http::class, $result);
        $this->assertStringContainsString('i18n', $result);
        $this->assertStringContainsString(\Spiral\Translator\TranslatorInterface::class, $result);
        $this->assertStringContainsString('input', $result);
        $this->assertStringContainsString(\Spiral\Http\Request\InputManager::class, $result);
        $this->assertStringContainsString('session', $result);
        $this->assertStringContainsString(\Spiral\Session\SessionScope::class, $result);
        $this->assertStringContainsString('cookies', $result);
        $this->assertStringContainsString(\Spiral\Cookies\CookieManager::class, $result);
        $this->assertStringContainsString('logger', $result);
        $this->assertStringContainsString(\Psr\Log\LoggerInterface::class, $result);
        $this->assertStringContainsString('logs', $result);
        $this->assertStringContainsString(\Spiral\Logger\LogsInterface::class, $result);
        $this->assertStringContainsString('memory', $result);
        $this->assertStringContainsString(\Spiral\Boot\MemoryInterface::class, $result);
        $this->assertStringContainsString('paginators', $result);
        $this->assertStringContainsString(\Spiral\Pagination\PaginationProviderInterface::class, $result);
        $this->assertStringContainsString('queue', $result);
        $this->assertStringContainsString(\Spiral\Queue\QueueInterface::class, $result);
        $this->assertStringContainsString('queueManager', $result);
        $this->assertStringContainsString(\Spiral\Queue\QueueConnectionProviderInterface::class, $result);
        $this->assertStringContainsString('request', $result);
        $this->assertStringContainsString(\Spiral\Http\Request\InputManager::class, $result);
        $this->assertStringContainsString('response', $result);
        $this->assertStringContainsString(\Spiral\Http\ResponseWrapper::class, $result);
        $this->assertStringContainsString('router', $result);
        $this->assertStringContainsString(\Spiral\Router\RouterInterface::class, $result);
        $this->assertStringContainsString('snapshots', $result);
        $this->assertStringContainsString(\Spiral\Snapshots\SnapshotterInterface::class, $result);
        $this->assertStringContainsString('storage', $result);
        $this->assertStringContainsString(\Spiral\Storage\BucketInterface::class, $result);
        $this->assertStringContainsString('serializer', $result);
        $this->assertStringContainsString(\Spiral\Serializer\SerializerManager::class, $result);
        $this->assertStringContainsString('validator', $result);
        $this->assertStringContainsString(\Spiral\Validation\ValidationInterface::class, $result);
        $this->assertStringContainsString('views', $result);
        $this->assertStringContainsString(\Spiral\Views\ViewsInterface::class, $result);
        $this->assertStringContainsString('auth', $result);
        $this->assertStringContainsString(\Spiral\Auth\AuthScope::class, $result);
        $this->assertStringContainsString('authTokens', $result);
        $this->assertStringContainsString(\Spiral\Auth\TokenStorageInterface::class, $result);
        $this->assertStringContainsString('cache', $result);
        $this->assertStringContainsString(\Psr\SimpleCache\CacheInterface::class, $result);
        $this->assertStringContainsString('cacheManager', $result);
        $this->assertStringContainsString(\Spiral\Cache\CacheStorageProviderInterface::class, $result);
        $this->assertStringContainsString('exceptionHandler', $result);
        $this->assertStringContainsString(\Spiral\Exceptions\ExceptionHandlerInterface::class, $result);
    }
}
