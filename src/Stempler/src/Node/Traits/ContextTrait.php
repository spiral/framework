<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Node\Traits;

use Spiral\Stempler\Parser\Context;

trait ContextTrait
{
    /** @var Context|null @internal */
    private $context;

    public function getContext(): ?Context
    {
        return $this->context;
    }
}
