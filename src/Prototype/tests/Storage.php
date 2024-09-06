<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype;

class Storage
{
    /** @var array */
    protected $storage = [];

    public function __construct(private string $dir)
    {
    }

    public function store(string $name): void
    {
        $this->storage[$name] = file_get_contents($this->dir . $name);
    }

    public function restore(string $name): void
    {
        file_put_contents($this->dir . $name, $this->storage[$name]);
    }
}
