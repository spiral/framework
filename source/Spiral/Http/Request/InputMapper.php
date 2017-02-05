<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Http\Request;

use Spiral\Http\Exceptions\InputException;
use Spiral\Validation\ValidatorInterface;

/**
 * Helper class needed to properly initiate request values and mount messages.
 */
class InputMapper
{
    /**
     * Default data source (POST).
     */
    const DEFAULT_SOURCE = 'data';

    /**
     * Used to define multiple nested models.
     */
    const NESTED_CLASS     = 0;
    const DATA_ORIGIN      = 1;
    const ITERATION_SOURCE = 2;

    /**
     * Input routing schema, must be compatible with RequestFilter.
     *
     * @var array
     */
    private $schema = [];

    /**
     * @param array $schema
     */
    public function __construct(array $schema)
    {
        $this->schema = $this->normalizeSchema($schema);
    }

    /**
     * Create set of values based on a given schema.
     *
     * @param InputInterface     $input
     * @param ValidatorInterface $validator Validator instance to be used for nested models.
     *
     * @return array
     */
    public function createValues(InputInterface $input, ValidatorInterface $validator = null): array
    {
        $result = [];
        foreach ($this->schema as $field => $map) {
            if (!empty($map['class'])) {
                $class = $map['class'];

                //Working with nested models
                if ($map['multiple']) {

                    //Let's create list of "key" => "location in request"
                    $origins = $this->createOrigins(
                        $input,
                        $field,
                        $map['origin'],
                        $map['iterate']
                    );

                    //Create model for each key in iteration list
                    foreach ($origins as $index => $origin) {
                        //New nested model in array of models over origins
                        $result[$field][$index] = new $class(
                            $input->withPrefix($origin),
                            !empty($validator) ? clone $validator : null
                        );
                    }

                    continue;
                }

                //Initiating sub model
                $result[$field] = new $class(
                    $input->withPrefix($map['origin']),
                    !empty($validator) ? clone $validator : null
                );

                continue;
            }

            //Reading value from input
            $result[$field] = $input->getValue($map['source'], $map['origin']);
        }

        return $result;
    }

    /**
     * Alter errors array so each field error associated with proper input origin name.
     *
     * @param array $errors
     *
     * @return array
     */
    public function originateErrors(array $errors): array
    {
        //De-mapping
        $mapped = [];
        foreach ($errors as $field => $message) {
            if (isset($this->schema[$field])) {
                //Mounting errors in a proper location
                $this->mountMessage($mapped, $this->schema[$field]['origin'], $message);
            } else {
                //Custom error
                $mapped[$field] = $mapped;
            }
        }

        return $mapped;
    }

    /**
     * Set element using dot notation.
     *
     * @param array  $array
     * @param string $path
     * @param mixed  $value
     *
     * @throws \Spiral\Http\Exceptions\InputException
     */
    protected function mountMessage(array &$array, string $path, $value)
    {
        if ($path == '.') {
            throw new InputException(
                "Invalid input location with error '{$value}', make sure to use proper pattern 'data:field_name'"
            );
        }

        $step = explode('.', $path);
        while ($name = array_shift($step)) {
            $array = &$array[$name];
        }

        $array = $value;
    }

    /**
     * Pre-processing schema in order to property define field mapping.
     *
     * @param array $schema
     *
     * @return array
     */
    protected function normalizeSchema(array $schema): array
    {
        $result = [];
        foreach ($schema as $field => $definition) {
            //Short definition
            if (is_string($definition)) {
                if (class_exists($definition)) {
                    //Singular nested model
                    $result[$field] = [
                        'class'    => $definition,
                        'source'   => null,
                        'origin'   => $field,
                        'multiple' => false
                    ];
                } else {
                    //Simple scalar field definition
                    list($source, $origin) = $this->parseDefinition($field, $definition);
                    $result[$field] = compact('source', 'origin');
                }

                continue;
            }

            //Complex definition
            if (is_array($definition)) {
                if (!empty($definition[self::DATA_ORIGIN])) {
                    $origin = $definition[self::DATA_ORIGIN];

                    //[class, 'data:something.*']
                    $multiple = strpos($origin, '.*') !== false;
                    $origin = rtrim($origin, '.*');
                } else {
                    $origin = $field;
                    $multiple = true;
                }

                // print_r()

                //Array of models (default isolation prefix)
                $map = [
                    'class'    => $definition[self::NESTED_CLASS],
                    'source'   => null,
                    'origin'   => $origin,
                    'multiple' => $multiple
                ];

                if ($multiple && !empty($definition[self::ITERATION_SOURCE])) {
                    //When multiple records we might have iteration flag
                    $map['iterate'] = $definition[self::ITERATION_SOURCE];
                } else {
                    //Default iteration over origin
                    $map['iterate'] = $map['origin'];
                }

                $result[$field] = $map;
            }
        }

        return $result;
    }

    /**
     * Fetch source name and origin from schema definition. Support forms:
     *
     * field => source        => source:field
     * field => source:origin => source:origin
     *
     * @param string $field
     * @param string $definition
     *
     * @return array [$source, $origin]
     */
    private function parseDefinition(string $field, string $definition): array
    {
        if (strpos($definition, ':') === false) {
            return [self::DEFAULT_SOURCE, $field, $definition];
        }

        return explode(':', $definition);
    }

    /**
     * Create set of origins and prefixed for a nested array of models.
     *
     * @param InputInterface $input
     * @param string         $field
     * @param string         $prefix
     * @param string         $iterate
     *
     * @return array
     */
    private function createOrigins(
        InputInterface $input,
        string $field,
        string $prefix,
        string $iterate
    ) {
        $result = [];

        list($source, $origin) = $this->parseDefinition($field, $iterate);

        $iteration = $input->getValue($source, $origin);
        if (empty($iteration) || !is_array($iteration)) {
            return [];
        }

        foreach (array_keys($iteration) as $key) {
            $result[$key] = $prefix . '.' . $key;
        }

        return $result;
    }
}