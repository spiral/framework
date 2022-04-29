<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node\Traits;

use Spiral\Stempler\Parser\Context;

trait ContextTrait
{
    private ?Context $context = null;

    public function getContext(): ?Context
    {
        return $this->context;
    }
}
