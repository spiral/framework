<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Auth;

use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\AuthScope;
use Spiral\Auth\Exception\AuthException;
use Spiral\Auth\TokenInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\FactoryInterface;

/**
 * Manages the set of actor providers.
 */
final class AuthBootloader extends Bootloader implements ActorProviderInterface, SingletonInterface
{
    protected const SINGLETONS = [
        AuthScope::class              => AuthScope::class,
        ActorProviderInterface::class => self::class
    ];

    /** @var FactoryInterface */
    private $factory;

    /** @var ActorProviderInterface[]|string[] */
    private $actorProvider = [];

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Find actor by first matching actor provider.
     *
     * @param TokenInterface $token
     * @return object|null
     */
    public function getActor(TokenInterface $token): ?object
    {
        foreach ($this->getProviders() as $provider) {
            if (!$provider instanceof ActorProviderInterface) {
                throw new AuthException(
                    sprintf(
                        'Expected `ActorProviderInterface`, got `%s`',
                        get_class($provider)
                    )
                );
            }

            $actor = $provider->getActor($token);
            if ($actor !== null) {
                return $actor;
            }
        }

        return null;
    }

    /**
     * Register new actor provider.
     *
     * @param ActorProviderInterface|Autowire|string $actorProvider
     */
    public function addActorProvider($actorProvider): void
    {
        $this->actorProvider[] = $actorProvider;
    }

    /**
     * @return \Generator|ActorProviderInterface[]
     */
    private function getProviders(): \Generator
    {
        foreach ($this->actorProvider as $provider) {
            if ($provider instanceof Autowire) {
                yield $provider->resolve($this->factory);
                continue;
            }

            if (is_object($provider)) {
                yield $provider;
                continue;
            }

            yield $this->factory->make($provider);
        }
    }
}
