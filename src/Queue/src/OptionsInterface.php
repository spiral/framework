<?php

declare(strict_types=1);

namespace Spiral\Queue;

interface OptionsInterface
{
    public function getDelay(): ?int;

    public function getQueue(): ?string;
}
