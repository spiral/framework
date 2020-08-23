<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use ReflectionException;
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

    /**
     * @var bool
     */
    private $hasDefault = false;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @param string       $name
     * @param mixed        $defaultValue
     * @param string|array $comment
     */
    public function __construct(string $name, $defaultValue = null, $comment = '')
    {
        $this->setName($name);
        if ($defaultValue !== null) {
            $this->setDefaultValue($defaultValue);
        }

        $this->initComment($comment);
    }

    /**
     * Has default value.
     *
     * @return bool
     */
    public function hasDefaultValue(): bool
    {
        return $this->hasDefault;
    }

    /**
     * Set default value.
     *
     * @param mixed $value
     * @return self
     */
    public function setDefaultValue($value): Property
    {
        $this->hasDefault = true;
        $this->defaultValue = $value;

        return $this;
    }

    /**
     * Remove default value.
     *
     * @return self
     */
    public function removeDefaultValue(): Property
    {
        $this->hasDefault = false;
        $this->defaultValue = null;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Replace comments.
     *
     * @param array|string $search
     * @param array|string $replace
     */
    public function replace($search, $replace): void
    {
        $this->docComment->replace($search, $replace);
    }

    /**
     * {@inheritdoc}
     * @throws ReflectionException
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

            if (is_array($this->defaultValue)) {
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
     *
     * @param string $serialized
     * @param int    $indentLevel
     * @return string
     */
    private function mountIndents(string $serialized, int $indentLevel): string
    {
        $lines = explode("\n", $serialized);
        foreach ($lines as &$line) {
            $line = $this->addIndent($line, $indentLevel);
            unset($line);
        }

        return ltrim(implode("\n", $lines));
    }
}
