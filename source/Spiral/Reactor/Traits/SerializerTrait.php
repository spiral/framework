<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Traits;

use Spiral\Support\Serializer;

/**
 * Manages Serializer object inside.
 */
trait SerializerTrait
{
    /**
     * @var Serializer|null
     */
    private $serializer = null;

    /**
     * Set custom serializer.
     *
     * @param Serializer $serializer
     */
    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Associated serializer.
     *
     * @return Serializer
     */
    private function serializer()
    {
        if (empty($this->serializer)) {
            $this->serializer = new Serializer();
        }

        return $this->serializer;
    }
}