<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations\Operations;

use Spiral\Database\Schemas\Prototypes\AbstractColumn;
use Spiral\Database\Schemas\Prototypes\AbstractTable;
use Spiral\Migrations\Exceptions\Operations\ColumnException;
use Spiral\Migrations\Operations\Traits\OptionsTrait;

/**
 * Generic column related operation.
 */
abstract class ColumnOperation extends TableOperation
{
    use OptionsTrait;

    /**
     * Some options has set of aliases.
     *
     * @var array
     */
    private $aliases = [
        'size'     => ['length', 'limit'],
        'default'  => ['defaultValue'],
        'nullable' => ['null']
    ];

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @param string|null $database
     * @param string      $table
     * @param string      $column
     * @param string      $type
     * @param array       $options
     */
    public function __construct(
        $database,
        string $table,
        string $column,
        string $type = 'string',
        array $options = []
    ) {
        parent::__construct($database, $table);

        $this->name = $column;
        $this->type = $type;
        $this->options = $options;
    }

    /**
     * @param AbstractTable $schema
     *
     * @return AbstractColumn
     * @throws ColumnException
     */
    protected function declareColumn(AbstractTable $schema): AbstractColumn
    {
        $column = $schema->column($this->name);

        //Type configuring
        if (method_exists($column, $this->type)) {
            $arguments = [];

            $method = new \ReflectionMethod($column, $this->type);
            foreach ($method->getParameters() as $parameter) {
                if ($this->hasOption($parameter->getName())) {
                    $arguments[] = $this->getOption($parameter->getName());
                } elseif (!$parameter->isOptional()) {
                    throw new ColumnException(
                        "Option '{$parameter->getName()}' are required to define column with type '{$this->type}'"
                    );
                } else {
                    $arguments[] = $parameter->getDefaultValue();
                }
            }

            call_user_func_array([$column, $this->type], $arguments);
        } else {
            $column->setType($this->type);
        }

        $column->nullable($this->getOption('nullable', false));
        $column->defaultValue($this->getOption('default', null));

        return $column;
    }
}