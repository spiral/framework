<?php

declare(strict_types=1);

namespace Spiral\Tests\Serializer\Fixture;

class SomeClass implements SomeInterface
{
    public int $id;
    public string $text;
    public bool $active;

    public function __construct(int $id, string $text, bool $active)
    {
        $this->id = $id;
        $this->text = $text;
        $this->active = $active;
    }
}
