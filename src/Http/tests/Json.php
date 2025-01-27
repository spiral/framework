<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

class Json implements \JsonSerializable
{
    public function __construct(private $data) {}

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->data;
    }
}
