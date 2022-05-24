<?php

declare (strict_types=1);

namespace MonorepoBuilder;

use Symplify\MonorepoBuilder\Contract\Git\TagResolverInterface;

final class MostRecentTagResolver implements TagResolverInterface
{
    /**
     * Always return null. Checking version moved to release worker.
     */
    public function resolve(string $gitDirectory): ?string
    {
        return null;
    }
}
