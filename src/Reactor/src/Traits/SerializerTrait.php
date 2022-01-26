<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Spiral\Reactor\Serializer;

/**
 * Manages Serializer object inside.
 */
trait SerializerTrait
{
    /**
     * @var Serializer|null
     */
    private $serializer;

    /**
     * Set custom serializer.
     */
    public function setSerializer(Serializer $serializer): void
    {
        $this->serializer = $serializer;
    }

    /**
     * Associated serializer.
     */
    private function getSerializer(): Serializer
    {
        return $this->serializer ?? ($this->serializer = new Serializer());
    }
}
