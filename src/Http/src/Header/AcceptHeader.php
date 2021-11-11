<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Pavel Z
 */

declare(strict_types=1);

namespace Spiral\Http\Header;

use Spiral\Http\Exception\AcceptHeaderException;

/**
 * Can be used for parsing and sorting "Accept*" header items by preferable by the HTTP client.
 *
 * Supported headers:
 *   Accept
 *   Accept-Encoding
 *   Accept-Charset
 *   Accept-Language
 */
final class AcceptHeader
{
    /** @var array|AcceptHeaderItem[] */
    private $items = [];

    /** @var bool */
    private $sorted = false;

    /**
     * AcceptHeader constructor.
     * @param AcceptHeaderItem[]|string[] $items
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    public function __toString(): string
    {
        return implode(', ', $this->getAll());
    }

    public static function fromString(string $raw): self
    {
        $header = new static();

        $parts = explode(',', $raw);
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part !== '') {
                $header->addItem($part);
            }
        }

        return $header;
    }

    /**
     * @param AcceptHeaderItem|string $item
     */
    public function add($item): self
    {
        $header = clone $this;
        $header->addItem($item);

        return $header;
    }

    public function has(string $value): bool
    {
        return isset($this->items[strtolower(trim($value))]);
    }

    public function get(string $value): ?AcceptHeaderItem
    {
        return $this->items[strtolower(trim($value))] ?? null;
    }

    /**
     * @return AcceptHeaderItem[]
     */
    public function getAll(): array
    {
        if (!$this->sorted) {
            /**
             * Sort item in descending order.
             */
            uasort($this->items, static function (AcceptHeaderItem $a, AcceptHeaderItem $b) {
                return self::compare($a, $b) * -1;
            });

            $this->sorted = true;
        }

        return array_values($this->items);
    }

    /**
     * Add new item to list.
     *
     * @param AcceptHeaderItem|string $item
     */
    private function addItem($item): void
    {
        if (is_scalar($item)) {
            $item = AcceptHeaderItem::fromString((string)$item);
        }

        $value = strtolower($item->getValue());
        if ($value !== '' && (!$this->has($value) || self::compare($item, $this->get($value)) === 1)) {
            $this->sorted = false;
            $this->items[$value] = $item;
        }
    }

    /**
     * Compare to header items, witch one is preferable.
     * Return 1 if first value preferable or -1 if second, 0 in case of same weight.
     *
     * @param AcceptHeaderItem|string $a
     * @param AcceptHeaderItem|string $b
     */
    private static function compare(AcceptHeaderItem $a, AcceptHeaderItem $b): int
    {
        if ($a->getQuality() === $b->getQuality()) {
            // If quality are same value with more params has more weight.
            if (count($a->getParams()) === count($b->getParams())) {
                // If quality and params then check for specific type or subtype.
                // Means */* or * has less weight.
                return static::compareValue($a->getValue(), $b->getValue());
            }

            return count($a->getParams()) <=> count($b->getParams());
        }

        return $a->getQuality() <=> $b->getQuality();
    }

    /**
     * Compare to header item values. More specific types ( with no "*" ) has more value.
     * Return 1 if first value preferable or -1 if second, 0 in case of same weight.
     */
    private static function compareValue(string $a, string $b): int
    {
        // Check "Accept" headers values with it is type and subtype.
        if (strpos($a, '/') !== false && strpos($b, '/') !== false) {
            [$typeA, $subtypeA] = explode('/', $a, 2);
            [$typeB, $subtypeB] = explode('/', $b, 2);

            if ($typeA === $typeB) {
                return static::compareAtomic($subtypeA, $subtypeB);
            }

            return static::compareAtomic($typeA, $typeB);
        }

        return static::compareAtomic($a, $b);
    }

    private static function compareAtomic(string $a, string $b): int
    {
        if (mb_strpos($a, '*/') === 0) {
            $a = '*';
        }

        if (mb_strpos($b, '*/') === 0) {
            $b = '*';
        }

        if (strtolower($a) === strtolower($b)) {
            return 0;
        }

        if ($a === '*') {
            return -1;
        }

        if ($b === '*') {
            return 1;
        }

        return 0;
    }
}
