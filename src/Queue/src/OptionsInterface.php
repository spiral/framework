<?php

declare(strict_types=1);

namespace Spiral\Queue;

interface OptionsInterface
{
    public function getPipeline(): ?string;
    public function getDelay(): ?int;
}
