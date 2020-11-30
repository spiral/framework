<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Cycle;

use Spiral\Boot\Memory;
use Spiral\Boot\MemoryInterface;

class TrackedMemory implements MemoryInterface
{
    /** @var int */
    public $saveCount = 0;
    /** @var Memory */
    private $memory;

    public function __construct(Memory $memory)
    {
        $this->memory = $memory;
    }

    public function loadData(string $section)
    {
        return $this->memory->loadData($section);
    }

    public function saveData(string $section, $data): void
    {
        $this->saveCount++;
        $this->memory->saveData($section, $data);
    }
}
