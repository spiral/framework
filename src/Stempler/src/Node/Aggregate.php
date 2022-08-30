<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Node;

use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * Aggregated multiple nodes together using public pattern.
 */
final class Aggregate implements NodeInterface
{
    use ContextTrait;

    /** @var string */
    public $pattern = '*';

    /** @var NodeInterface[] */
    public $nodes = [];

    /** @var array */
    public $uniqueIDs = [];

    /**
     * @param Context|null $context
     */
    public function __construct(Context $context = null, string $pattern = '*')
    {
        $this->context = $context;
        $this->pattern = $pattern;
    }

    /**
     * Checks if aggregate can accept given node by it's name and return final name (if prefix presented),
     * or return null.
     */
    public function accepts(string $name): ?string
    {
        if ($this->pattern === '' || $this->pattern === '*') {
            // accept everything
            return $name;
        }

        $conditions = [];
        foreach (explode(';', $this->pattern) as $condition) {
            if (strpos($condition, ':') === false) {
                //Invalid
                continue;
            }

            [$option, $value] = explode(':', trim($condition));
            $conditions[$option] = $value;
        }

        if (isset($conditions['include'])) {
            $include = explode(',', $conditions['include']);
            if (in_array($name, $include)) {
                return $name;
            }

            return null;
        }

        if (isset($conditions['exclude'])) {
            $exclude = explode(',', $conditions['exclude']);
            if (in_array($name, $exclude)) {
                return null;
            }

            return $name;
        }

        if (isset($conditions['prefix'])) {
            $conditions['prefix'] = rtrim($conditions['prefix'], ' *');
            if (strpos($name, $conditions['prefix']) === 0) {
                return substr($name, strlen($conditions['prefix']));
            }

            return null;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): \Generator
    {
        yield 'nodes' => $this->nodes;
    }
}
