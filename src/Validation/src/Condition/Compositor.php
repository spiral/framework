<?php

/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Validation\Condition;

use Spiral\Validation\ConditionInterface;
use Spiral\Validation\RulesInterface;

/**
 * @internal
 */
final class Compositor
{
    /** @var RulesInterface $provider */
    private $provider;

    /**
     * @param RulesInterface $provider
     */
    public function __construct(RulesInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param string $field
     * @param array  $options
     * @return ConditionInterface[]
     */
    public function makeConditions(string $field, array $options): iterable
    {
        $rules = $this->provider->getRules([
            $field => [
                static function (): void {
                },
                'if' => $options,
            ],
        ]);

        foreach ($rules as $rule) {
            return $rule->getConditions();
        }

        return [];
    }
}
