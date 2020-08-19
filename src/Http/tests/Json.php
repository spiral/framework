<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Http;

class Json implements \JsonSerializable
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}
