<?php

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
        ActorProviderInterface::class => self::class,
    ];

    /** @var array<int, ActorProviderInterface|Autowire|string> */
    private array $actorProvider = [];

    public function __construct(
        private readonly FactoryInterface $factory
    ) {
    }

    /**
     * Find actor by first matching actor provider.
     */
    public function getActor(TokenInterface $token): ?object
    {
        foreach ($this->getProviders() as $provider) {
            if (!$provider instanceof ActorProviderInterface) {
                throw new AuthException(
                    \sprintf(
                        'Expected `ActorProviderInterface`, got `%s`',
                        $provider::class
                    )
                );
            }

            $actor = $provider->getActor($token);
            if ($actor !== null) {
                return $actor;
            }
        }

        if ($this->actorProvider === []) {
            throw new AuthException('No actor provider');
        }

        return null;
    }

    /**
     * Register new actor provider.
     */
    public function addActorProvider(ActorProviderInterface|Autowire|string $actorProvider): void
    {
        $this->actorProvider[] = $actorProvider;
    }

    /**
     * @return \Generator<int, ActorProviderInterface>
     */
    private function getProviders(): \Generator
    {
        foreach ($this->actorProvider as $provider) {
            if ($provider instanceof Autowire) {
                yield $provider->resolve($this->factory);
                continue;
            }

            if (\is_object($provider)) {
                yield $provider;
                continue;
            }

            yield $this->factory->make($provider);
        }
    }
}
