<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Input;

use Spiral\Components\Http\InputManager;
use Spiral\Support\Models\DataEntity;

abstract class ActionRequest extends DataEntity
{
    /**
     * Request data schema. Schema used to describe set of data should be used by request or any sub
     * validators.
     * Format (defines filter and data source):
     * type[::source(name)] - where source is name of access method in InputManager.
     *
     * Examples:
     * string::post               - field name will be used to perform post lookup
     * int::query(name)           - int field will be fetched from query variable under name "name".
     * object::file              - field name will be used to perform files lookup
     * string::cookie            - field name will be used to perform cookies lookup
     * string::header(User-Agent) - value of User-Agent header, casted to string.
     *
     * Schema example:
     * protected $schema = [
     *      'name'      => 'string::post',
     *      'amount'    => 'float::post',
     *      'sessionID' => 'string::cookie'
     * ];
     *
     * @var array
     */
    protected $schema = [
        'name'      => 'string::post',
        'amount'    => 'float::post',
        'sendEmail' => 'bool::post',
        'sessionID' => 'string::cookie'
    ];

    /**
     * Set of mutators will be used to create setters, getters and accessors based on field type.
     *
     * @var array
     */
    protected $mutators = [
        'int'       => ['setter' => 'intval'],
        'float'     => ['setter' => 'floatval'],
        'string'    => ['setter' => 'string'],
        'bool'      => ['setter' => 'boolean'],
        'array'     => ['setter' => 'scalarArray'],
        'timestamp' => ['accessor' => 'Spiral\Support\Models\Accessors\Timestamp']
    ];

    /**
     * New instance of action request.
     *
     * @param InputManager $input
     */
    public function __construct(InputManager $input)
    {
        //Create default setters, getters and accessors
        $this->buildFilters();

        //Filling data
        $this->fetchData($input);
    }

    /**
     * Build setters, getters and assessors filters based on declared input types.
     */
    protected function buildFilters()
    {
        foreach ($this->schema as $field => $definition)
        {
            $definition = explode('::', $definition);

            if (isset($this->mutators[$definition[0]]))
            {
                //Registering mutator
                foreach ($this->mutators[$definition[0]] as $mutator => $filter)
                {
                    //We do support 3 mutators: getter, setter and accessor, all of them can be
                    //referenced to valid field name by adding "s" at the end
                    $mutators = $mutator . 's';

                    if (!array_key_exists($field, $this->{$mutators}))
                    {
                        $this->{$mutators}[$field] = $filter;
                    }
                }
            }
        }
    }

    /**
     * Fetch data from appropriate part of input request.
     *
     * @param InputManager $input
     */
    protected function fetchData(InputManager $input)
    {
        foreach ($this->schema as $field => $definition)
        {
            if (preg_match(
                '/(?P<type>[a-z]+)::(?P<source>[a-z]+)(?:\((?P<name>.+)\))?/',
                $definition,
                $matches
            ))
            {
                $name = isset($matches['name']) ? $matches['name'] : $field;
                $source = $matches['source'];

                //Getting value from appropriate source
                $this->setField($field, $input->{$source}($name));
            }
        }
    }

    /**
     * Simplified way to dump information.
     *
     * @return Object
     */
    public function __debugInfo()
    {
        return (object)[
            'fields' => $this->getFields(),
            'errors' => $this->getErrors()
        ];
    }
}