<?php

declare(strict_types=1);

namespace Spiral\Debug;

interface StateConsumerInterface
{
    public function withState(StateInterface $state): static;
}
