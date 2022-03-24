<?php

declare(strict_types=1);

namespace Spiral\Validation\Condition;

use Spiral\Validation\ConditionInterface;
use Spiral\Validation\RulesInterface;

/**
 * @internal
 */
final class Compositor
{
    public function __construct(
        private readonly RulesInterface $provider
    ) {
    }

    /**
     * @return iterable<ConditionInterface>
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
