<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Security;

use ReflectionMethod;
use ReflectionException;
use Throwable;
use Spiral\Core\ResolverInterface;
use Spiral\Security\Exception\RuleException;

/**
 * Rule class provides ability to verify permission request via specified method (by default
 * "check") using resolver interface. As side effect check method will support method injections.
 *
 * Example:
 *
 * class MyRule extends Rule
 * {
 *      public function check($actor, $post)
 *      {
 *          return $post->author_id == $actor->id;
 *      }
 * }
 */
abstract class Rule implements RuleInterface
{
    //Method to be used for checking (support for method injection).
    protected const CHECK_METHOD = 'check';

    /**
     * Set of aliases to be used for method injection.
     *
     * @var array
     */
    protected const ALIASES = [
        'user' => 'actor',
    ];

    /* @var ResolverInterface */
    protected $resolver;

    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuleException
     */
    public function allows(ActorInterface $actor, string $permission, array $context): bool
    {
        $parameters = compact('actor', 'permission', 'context') + $context;

        //Mounting aliases
        foreach (static::ALIASES as $target => $alias) {
            $parameters[$target] = $parameters[$alias];
        }

        try {
            $method = new ReflectionMethod($this, static::CHECK_METHOD);
            $method->setAccessible(true);
        } catch (ReflectionException $e) {
            throw new RuleException($e->getMessage(), $e->getCode(), $e);
        }

        try {
            return $method->invokeArgs($this, $this->resolver->resolveArguments($method, $parameters));
        } catch (Throwable $e) {
            throw new RuleException(sprintf('[%s] %s', get_class($this), $e->getMessage()), $e->getCode(), $e);
        }
    }
}
