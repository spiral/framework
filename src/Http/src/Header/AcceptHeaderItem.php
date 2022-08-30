<?php

declare(strict_types=1);

namespace Spiral\Http\Header;

/**
 * Represents "Accept" header single item.
 *
 * Can be used for comparing each item weight or constructing the "Accept" headers.
 */
final class AcceptHeaderItem implements \Stringable
{
    private string $value;
    private float $quality;
    private array $params = [];

    /**
     * AcceptHeaderItem constructor.
     */
    public function __construct(string $mime, float $quality = 1.0, array $params = [])
    {
        $this->setValue($mime);
        $this->setQuality($quality);
        $this->setParams($params);
    }

    public function __toString(): string
    {
        if ($this->value === '') {
            return '';
        }

        $parts = [$this->value];

        if ($this->quality < 1) {
            $parts[] = \sprintf('q=%s', $this->quality);
        }

        foreach ($this->getParams() as $name => $value) {
            $parts[] = \sprintf('%s=%s', $name, $value);
        }

        return \implode('; ', $parts);
    }

    /**
     * Parse accept header string.
     */
    public static function fromString(string $string): self
    {
        $elements = \explode(';', $string);

        $mime = \trim(\array_shift($elements));
        $quality = 1.0;
        $params = [];

        foreach ($elements as $element) {
            $parsed = \explode('=', \trim($element), 2);

            // Wrong params must be ignored
            if (\count($parsed) !== 2) {
                continue;
            }

            $name = \trim($parsed[0]);
            $value = \trim($parsed[1]);

            if (\strcasecmp($name, 'q') === 0) {
                $quality = (float)$value;
            } else {
                $params[$name] = $value;
            }
        }

        return new self($mime, $quality, $params);
    }

    public function withValue(string $value): self
    {
        $item = clone $this;
        $item->setValue($value);

        return $item;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function withQuality(float $quality): self
    {
        $item = clone $this;
        $item->setQuality($quality);

        return $item;
    }

    public function getQuality(): float
    {
        return $this->quality;
    }

    public function withParams(array $params): self
    {
        $item = clone $this;
        $item->setParams($params);

        return $item;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    private function setValue(string $value): void
    {
        $this->value = \trim($value);
    }

    private function setQuality(float $quality): void
    {
        $this->quality = \min(\max($quality, 0), 1);
    }

    private function setParams(array $params): void
    {
        foreach ($params as $name => $value) {
            if (\is_numeric($name) || !\is_scalar($value)) {
                continue;
            }

            $name = \trim($name);
            $value = \trim((string)$value);

            if ($name === '' || $value === '') {
                continue;
            }

            $this->params[$name] = $value;
        }
    }
}
