<?php

declare(strict_types=1);

namespace Spiral\Queue;

interface OptionsInterface
{
    /**
     * @return positive-int|null
     */
    public function getDelay(): ?int;

    /**
     * @return non-empty-string|null
     */
    public function getQueue(): ?string;
}
