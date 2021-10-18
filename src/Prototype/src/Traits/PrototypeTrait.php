<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\Traits;

use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\ScopeException;
use Spiral\Prototype\Exception\PrototypeException;
use Spiral\Prototype\PrototypeRegistry;

/**
 * This DocComment is auto-generated, do not edit or commit this file to repository.
 *
 * @property \App\App $app
 * @property \Spiral\Tokenizer\ClassesInterface $classLocator
 * @property \Spiral\Console\Console $console
 * @property \Psr\Container\ContainerInterface $container
 * @property \Spiral\Database\DatabaseInterface $db
 * @property \Spiral\Database\DatabaseProviderInterface $dbal
 * @property \Spiral\Encrypter\EncrypterInterface $encrypter
 * @property \Spiral\Boot\EnvironmentInterface $env
 * @property \Spiral\Files\FilesInterface $files
 * @property \Spiral\Security\GuardInterface $guard
 * @property \Spiral\Http\Http $http
 * @property \Spiral\Translator\TranslatorInterface $i18n
 * @property \Spiral\Http\Request\InputManager $input
 * @property \Spiral\Session\SessionScope $session
 * @property \Spiral\Cookies\CookieManager $cookies
 * @property \Psr\Log\LoggerInterface $logger
 * @property \Spiral\Logger\LogsInterface $logs
 * @property \Spiral\Boot\MemoryInterface $memory
 * @property \Cycle\ORM\ORMInterface $orm
 * @property \Spiral\Pagination\PaginationProviderInterface $paginators
 * @property \Spiral\Jobs\QueueInterface $queue
 * @property \Spiral\Http\Request\InputManager $request
 * @property \Spiral\Http\ResponseWrapper $response
 * @property \Spiral\Router\RouterInterface $router
 * @property \Spiral\Snapshots\SnapshotterInterface $snapshots
 * @property \Spiral\Storage\BucketInterface $storage
 * @property \Spiral\Validation\ValidationInterface $validator
 * @property \Spiral\Views\ViewsInterface $views
 * @property \Spiral\Auth\AuthScope $auth
 * @property \Spiral\Auth\TokenStorageInterface $authTokens
 */
trait PrototypeTrait
{
    /**
     * Automatic resolution of scoped dependency to it's value. Relies
     * on global container scope.
     *
     * @param string $name
     * @return mixed
     *
     * @throws ScopeException
     */
    public function __get(string $name)
    {
        $container = ContainerScope::getContainer();
        if ($container === null || !$container->has(PrototypeRegistry::class)) {
            throw new ScopeException(
                "Unable to resolve prototyped dependency `{$name}`, invalid container scope"
            );
        }

        /** @var PrototypeRegistry $registry */
        $registry = $container->get(PrototypeRegistry::class);

        $target = $registry->resolveProperty($name);
        if (
            $target === null ||
            $target instanceof \Throwable ||
            $target->type->fullName === null
        ) {
            throw new PrototypeException(
                "Undefined prototype property `{$name}`",
                0,
                $target instanceof \Throwable ? $target : null
            );
        }

        return $container->get($target->type->name());
    }
}
