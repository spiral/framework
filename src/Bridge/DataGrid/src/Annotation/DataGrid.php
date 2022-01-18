<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Annotation;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Spiral\Attributes\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"METHOD"})
 * @Attributes({
 *     @Attribute("grid", required=true, type="string"),
 *     @Attribute("view", type="string"),
 *     @Attribute("defaults", type="array"),
 *     @Attribute("options", type="array"),
 *     @Attribute("factory", type="string")
 * })
 */
#[\Attribute(\Attribute::TARGET_METHOD), NamedArgumentConstructor]
class DataGrid
{
    /**
     * Points to grid schema.
     *
     * @type string
     */
    public $grid;

    /**
     * Response options, default to GridSchema->__invoke() if such method exists.
     *
     * @var string
     */
    public $view;

    /**
     * Response options, default to GridSchema->getDefaults() if such method exists.
     *
     * @var array
     */
    public $defaults = [];

    /**
     * Response options, default to GridSchema->getOptions() if such method exists.
     *
     * @var array
     */
    public $options = [];

    /**
     * Custom user GridFactory
     *
     * @var string
     */
    public $factory;

    public function __construct(
        string $grid,
        ?string $view = null,
        array $defaults = [],
        array $options = [],
        ?string $factory = null
    ) {
        $this->grid = $grid;
        $this->view = $view;
        $this->defaults = $defaults;
        $this->options = $options;
        $this->factory = $factory;
    }
}
