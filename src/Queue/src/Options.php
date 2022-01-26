<?php

declare(strict_types=1);

namespace Spiral\Queue;

final class Options implements OptionsInterface, \JsonSerializable
{
    /** @var int|null */
    private $delay;

    /**
     * @param int $delay
     */
    public function withDelay(?int $delay): self
    {
        $options = clone $this;
        $options->delay = $delay;

        return $options;
    }

    public function getDelay(): ?int
    {
        return $this->delay;
    }

    /**
     * @return array|mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'delay'    => $this->delay,
        ];
    }

    public static function delayed(int $delay): Options
    {
        $options = new self();
        $options->delay = $delay;

        return $options;
    }
}
