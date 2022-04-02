<?php

declare(strict_types=1);

namespace Spiral\Models;

final class ModelSchema
{
    public const SECURED  = 2;
    public const FILLABLE = 3;
    public const MUTATORS = 4;

    public const MUTATOR_GETTER   = 'getter';
    public const MUTATOR_SETTER   = 'setter';
    public const MUTATOR_ACCESSOR = 'accessor';
}
