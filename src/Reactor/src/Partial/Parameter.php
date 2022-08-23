<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Doctrine\Inflector\Rules\English\InflectorFactory;
use ReflectionException;
use Spiral\Reactor\AbstractDeclaration;
use Spiral\Reactor\NamedInterface;
use Spiral\Reactor\Traits\NamedTrait;
use Spiral\Reactor\Traits\SerializerTrait;

/**
 * Single method parameter.
 */
class Parameter extends AbstractDeclaration implements NamedInterface
{
    use NamedTrait;
    use SerializerTrait;

    /** @var string */
    private $type = '';

    /** @var bool */
    private $isOptional = false;

    /** @var mixed */
    private $defaultValue;

    /** @var bool */
    private $pdb = false;

    public function __construct(string $name)
    {
        $this->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name): Parameter
    {
        $this->name = (new InflectorFactory())->build()->camelize($name);

        return $this;
    }

    public function setType(string $type): Parameter
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Flag that parameter should pass by reference.
     */
    public function setPBR(bool $passedByReference = false): Parameter
    {
        $this->pdb = $passedByReference;

        return $this;
    }

    public function isPBR(): bool
    {
        return $this->pdb;
    }

    /**
     * Check if parameter is optional.
     */
    public function isOptional(): bool
    {
        return $this->isOptional;
    }

    /**
     * Set parameter default value.
     *
     * @param mixed $defaultValue
     */
    public function setDefaultValue($defaultValue): Parameter
    {
        $this->isOptional = true;
        $this->defaultValue = $defaultValue;

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
     * Remove default value.
     */
    public function removeDefaultValue(): Parameter
    {
        $this->isOptional = false;
        $this->defaultValue = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @throws ReflectionException
     */
    public function render(int $indentLevel = 0): string
    {
        $type = '';
        if (!empty($this->type)) {
            $type = $this->type . ' ';
        }

        $result = $type . ($this->pdb ? '&' : '') . '$' . $this->getName();

        if (!$this->isOptional) {
            return $result;
        }

        return $result . ' = ' . $this->getSerializer()->serialize($this->defaultValue);
    }
}
