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
use Spiral\Reactor\Traits\AccessTrait;
use Spiral\Reactor\Traits\CommentTrait;
use Spiral\Reactor\Traits\NamedTrait;
use Spiral\Reactor\Traits\SerializerTrait;

/**
 * Class constant declaration.
 */
class Constant extends AbstractDeclaration implements NamedInterface
{
    use NamedTrait;
    use CommentTrait;
    use SerializerTrait;
    use AccessTrait;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string       $value
     * @param string|array $comment
     */
    public function __construct(string $name, $value, $comment = '')
    {
        $this->setName($name);
        $this->value = $value;
        $this->initComment($comment);
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name): Constant
    {
        $this->name = strtoupper(
            (new InflectorFactory())
                ->build()
                ->tableize(strtolower($name))
        );

        return $this;
    }

    /**
     * Array values allowed (but works in PHP7 only).
     *
     * @param mixed $value
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
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

        $result .= $this->addIndent("{$this->access} const {$this->getName()} = ", $indentLevel);

        $value = $this->getSerializer()->serialize($this->value);
        if (is_array($this->value)) {
            $value = $this->mountIndents($value, $indentLevel);
        }

        return $result . "{$value};";
    }

    /**
     * Mount indentation to value. Attention, to be applied to arrays only!
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
