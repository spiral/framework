<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype;

use Spiral\Prototype\Traits\PrototypeTrait;
use Spiral\Tokenizer\ClassesInterface;

final class PrototypeLocator
{
    /** @var ClassesInterface */
    private $classes;

    /**
     * @param ClassesInterface $classes
     */
    public function __construct(ClassesInterface $classes)
    {
        $this->classes = $classes;
    }

    /**
     * Locate all classes requiring de-prototyping.
     *
     * @return \ReflectionClass[]
     */
    public function getTargetClasses(): array
    {
        return $this->classes->getClasses(PrototypeTrait::class);
    }
}
