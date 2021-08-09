<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;
use Spiral\Attributes\NamedArgumentConstructor;

/**
 * @Annotation()
 * @Annotation\Target({"METHOD"})
 * @NamedArgumentConstructor()
 */
#[Attribute(Attribute::TARGET_METHOD)]
#[NamedArgumentConstructor()]
class DataGrid
{
    /**
     * Points to grid schema.
     *
     * @Annotation\Attribute(name="grid", type="string", required=true)
     * @var string
     */
    public $grid;

    /**
     * Response options, default to GridSchema->__invoke() if such method exists.
     *
     * @Annotation\Attribute(name="view", type="string")
     * @var null|string
     */
    public $view;

    /**
     * Response options, default to GridSchema->getDefaults() if such method exists.
     *
     * @Annotation\Attribute(name="defaults", type="array")
     * @var array
     */
    public $defaults;

    /**
     * Response options, default to GridSchema->getOptions() if such method exists.
     *
     * @Annotation\Attribute(name="options", type="array")
     * @var array
     */
    public $options;

    /**
     * Custom user GridFactory
     *
     * @Annotation\Attribute(name="factory", type="string")
     * @var null|string
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
