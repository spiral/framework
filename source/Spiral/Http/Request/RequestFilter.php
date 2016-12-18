<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Http\Request;

use Spiral\Http\Exceptions\InputException;
use Spiral\Models\ValidatesEntity;
use Spiral\Validation\ValidatorInterface;

/**
 * Request filter is data entity which uses input manager to populate it's fields, model can
 * perform input filtering, value routing (query, data, files) and validation.
 *
 * Attention, you can not inherit one request from another at this moment. You can use generic
 * validation rules for your input fields.
 *
 * Please do not request instance without using container, constructor signature might change over
 * time (or another request filter class can be created with inheritance and composition support).
 *
 * Example schema definition:
 * const SCHEMA = [
 *      'name'   => 'post:name',                              //identical to "data:name"
 *      'field'  => 'query',                                  //field name will used as search
 *                                                            //criteria in query ("query:field")
 *      'file'   => 'file:images.preview',                    //Yep, that's too
 *      'secure' => 'isSecure'                                //Alias for InputManager->isSecure(),
 *      'uploads' => [UploadFilter::class, "file:uploads.*"], //Iterate over files:uploads array
 *      'address' => AddressRequest::class,                   //Nested model associated with
 *                                                            //address subset of data
 *      'address' => [AddressRequest::class, "data:address"]  //Identical to previous definition
 * ];
 *
 * You can declare as source (query, file, post and etc) as source plus origin name (file:files.0).
 * Available sources: uri, path, method, isSecure, isAjax, isJsonExpected, remoteAddress.
 * Plus named sources (bags): header, data, post, query, cookie, file, server, attribute.
 *
 * @todo HIERARCHICAL VALIDATION
 */
class RequestFilter extends ValidatesEntity
{
    /**
     * Default data origin (POST).
     */
    const DEFAULT_SOURCE = 'data';

    /**
     * Defines request data mapping (input => request property)
     */
    const SCHEMA = [];

    /**
     * @param InputInterface     $input
     * @param ValidatorInterface $validator
     */
    public function __construct(InputInterface $input = null, ValidatorInterface $validator = null)
    {
        //We are going to populate input fields manually
        parent::__construct([], $validator);

        if (!empty($input)) {
            $this->initValues($input, $validator);
        }
    }

    /**
     * Set request values based on a given input interface.
     *
     * @param InputInterface     $input
     * @param ValidatorInterface $validator Validator to propagate to sub entities. To be cloned.
     */
    public function initValues(InputInterface $input, ValidatorInterface $validator = null)
    {
        //Emptying the model
        $this->flushValues();

        if (empty(static::SCHEMA)) {
            throw new InputException("Unable to initiate RequestFilter with empty schema");
        }

        foreach (static::SCHEMA as $field => $source) {
            list($xsource, $origin, $class, $iterate) = $this->parseSource($field, $source);

            if (!empty($class)) {
                if ($iterate) {
                    $models = [];
                    foreach ($this->createOrigins() as $index => $origin) {
                        $models[$index] = new $class(
                            $input->withPrefix($origin),
                            !empty($validator) ? clone $validator : null
                        );
                    }

                    $this->setField($field, $models, false);

                    continue;
                }

                //Let's initiate sub model
                $model = new $class(
                    $input->withPrefix($origin),
                    !empty($validator) ? clone $validator : null
                );

                $this->setField($field, $model, false);
                continue;
            }

            //Set data value based on input (enable filtering)
            $this->setField($field, $input->getValue($xsource, $origin), true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors(): array
    {
        //De-mapping
        $errors = [];
        foreach (parent::getErrors() as $field => $message) {
            list(, $origin) = $this->parseSource($field, static::SCHEMA[$field]);

            if ($field == $origin) {
                $errors[$field] = $message;
            } else {
                //Let's recreate original structure
                self::mountMessage($errors, $origin, $message);
            }
        }

        return $errors;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'schema' => static::SCHEMA,
            'fields' => $this->getFields(),
            'errors' => $this->getErrors()
        ];
    }

    /**
     * Fetch source name and origin from schema definition.
     *
     * @param string $field
     * @param mixed  $source
     *
     * @return array [$source, $origin, $class, $iterate]
     */
    private function parseSource(string $field, $source): array
    {
        if (is_array($source)) {
            if (count($source) == 1) {
                //Short definition
                return [
                    null,       //Does not needed due prefix based isolation
                    $field,     //Using field as origin
                    $source[0], //Class are given to us in a schema
                    true        //Iteration is needed
                ];
            }

            return [
                null,    //Does not needed due prefix based isolation
                $field,  //Using field as origin
                $source,   //Class are given to us in a schema
                true     //Iteration is needed
            ];
        }

        if (class_exists($source)) {
            return [
                null,    //Does not needed due prefix based isolation
                $field,  //Using field as origin
                $source, //Class are given to us in a schema
                false    //No iteration
            ];
        }

        if (strpos($source, ':') === false) {
            return [
                self::DEFAULT_SOURCE, //Using default source
                $field,               //What is origin field name
                null,                 //Not sub model
                false                 //No iteration
            ];
        }

        list($source, $origin) = explode(':', $source);

        return [
            $source, //What is data source
            $origin, //What is origin field name
            null,    //Not sub model
            false    //No iterations
        ];
    }

    private function createOrigins()
    {
        return [
            0 => 'files.0',
            1 => 'files.1',
            2 => 'files.2'
        ];
    }

    /**
     * Set element using dot notation.
     *
     * @param array  $array
     * @param string $path
     * @param mixed  $value
     */
    private static function mountMessage(array &$array, string $path, $value)
    {
        $step = explode('.', $path);
        while ($name = array_shift($step)) {
            $array = &$array[$name];
        }

        $array = $value;
    }
}