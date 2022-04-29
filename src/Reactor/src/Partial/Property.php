<?php

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Spiral\Reactor\AbstractDeclaration;
use Spiral\Reactor\NamedInterface;
use Spiral\Reactor\ReplaceableInterface;
use Spiral\Reactor\Traits\AccessTrait;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Reactor\Traits\NamedTrait;
use Spiral\Reactor\Traits\SerializerTrait;

/**
 * Declares property element.
 */
class Property extends AbstractDeclaration implements ReplaceableInterface, NamedInterface
{
    use NamedTrait;
    use CommentTrait;
    use SerializerTrait;
    use AccessTrait;

    private bool $hasDefault = false;
    private mixed $defaultValue = null;

    public function __construct(string $name, mixed $defaultValue = null, array|string $comment = '')
    {
        $this->setName($name);
        if ($defaultValue !== null) {
            $this->setDefaultValue($defaultValue);
        }

        $this->initComment($comment);
    }

    /**
     * Has default value.
     */
    public function hasDefaultValue(): bool
    {
        return $this->hasDefault;
    }

    /**
     * Set default value.
     */
    public function setDefaultValue(mixed $value): Property
    {
        $this->hasDefault = true;
        $this->defaultValue = $value;

        return $this;
    }

    /**
     * Remove default value.
     */
    public function removeDefaultValue(): Property
    {
        $this->hasDefault = false;
        $this->defaultValue = null;

        return $this;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * Replace comments.
     */
    public function replace(array|string $search, array|string $replace): Property
    {
        $this->docComment->replace($search, $replace);

        return $this;
    }

    /**
     * @throws \ReflectionException
     */
    public function render(int $indentLevel = 0): string
    {
        $result = '';
        if (!$this->docComment->isEmpty()) {
            $result .= $this->docComment->render($indentLevel) . "\n";
        }

        $result .= $this->addIndent("{$this->access} \${$this->getName()}", $indentLevel);

        if ($this->hasDefault) {
            $value = $this->getSerializer()->serialize($this->defaultValue);

            if (\is_array($this->defaultValue)) {
                $value = $this->mountIndents($value, $indentLevel);
            }

            $result .= " = {$value};";
        } else {
            $result .= ';';
        }

        return $result;
    }

    /**
     * Mount indentation to value. Attention, to be applied to arrays only!
     */
    private function mountIndents(string $serialized, int $indentLevel): string
    {
        $lines = \explode("\n", $serialized);
        foreach ($lines as &$line) {
            $line = $this->addIndent($line, $indentLevel);
            unset($line);
        }

        return \ltrim(\implode("\n", $lines));
    }
}
