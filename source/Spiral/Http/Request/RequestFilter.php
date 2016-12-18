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
        $this->flushFields();

        if (empty(static::SCHEMA)) {
            throw new InputException("Unable to initiate RequestFilter with empty schema");
        }

        foreach (static::SCHEMA as $field => $source) {
            list($source, $origin) = $this->parseSource($field, $source);

            if (!empty($class)) {
                //Let's initiate sub model
                $model = new $class(
                    $input->withPrefix($source),
                    !empty($validator) ? clone $validator : null
                );

                //todo: iterate over

                $this->setField($field, $model, false);
                continue;
            }

            //Set data value based on input (enable filtering)
            $this->setField($field, $input->getValue($source, $origin), true);
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
                $this->mapMessage($errors, $origin, $message);
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
        if (strpos($source, ':') === false) {
            return [$source, $field];
        }

        return explode(':', $source);
    }

    /**
     * Set element using dot notation.
     *
     * @param array  $array
     * @param string $path
     * @param mixed  $value
     */
    private function mapMessage(array &$array, string $path, $value)
    {
        $step = explode('.', $path);
        while ($name = array_shift($step)) {
            $array = &$array[$name];
        }

        $array = $value;
    }
}