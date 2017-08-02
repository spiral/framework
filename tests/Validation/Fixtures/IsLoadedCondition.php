<?php

namespace Spiral\Tests\Validation\Fixtures;

use Spiral\ORM\Record;
use Spiral\Validation\Prototypes\AbstractCheckerCondition;

class IsLoadedCondition extends AbstractCheckerCondition
{
    /**
     * This current condition tells that it will met if passed validator context is a loaded Record entity.
     *
     * @return bool
     */
    public function isMet(): bool
    {
        $entity = $this->validator->getContext();

        return !empty($entity) && ($entity instanceof Record) && $entity->isLoaded();
    }
}