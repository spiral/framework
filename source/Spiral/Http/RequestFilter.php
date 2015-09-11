<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Http;

use Spiral\Core\ContainerInterface;
use Spiral\Core\Service;
use Spiral\Http\Exceptions\FilterException;
use Spiral\Models\DataEntity;
use Spiral\Models\EntityInterface;

/**
 * Request filter is data entity which uses input manager to populate it's fields, model can perform
 * input filtering, value routing (query, data, files) and filtering.
 *
 * Attention, you can not inherit one request from another at this moment. You can use generic
 * validation rules for your input fields.
 *
 * Example schema definition:
 * protected $schema = [
 *      'name'   => 'post:name',           //identical to "data:name"
 *      'field'  => 'query',               //field name will used as search criteria in query
 *      'file'   => 'file:images.preview', //Yep, that's too
 *      'secure' => 'isSecure'             //Alias for InputManager->isSecure()
 * ];
 *
 * You can declare as source (query, file, post and etc) as source plus origin name (file:files.0).
 * Available sources: uri, path, method, isSecure, isAjax, isJsonExpected, remoteAddress.
 * Plus named sources (bags): header, data, post, query, cookie, file, server, attribute.
 *
 * There is possibility that this class and it's schema will behave same way as ORM and ODM models
 * one day.
 */
class RequestFilter extends DataEntity
{
    /**
     * Request filter makes every field settable.
     *
     * @var array
     */
    protected $secured = [];

    /**
     * Request schema declares field names, their source and origin name if any. See examples in
     * class header.
     *
     * @var array
     */
    protected $schema = [];

    /**
     * @invisible
     * @var InputManager
     */
    protected $input = null;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * Please do not construct this class by yourself for now, always use container as i'm working
     * on new request filter functionality including nested requests.
     *
     * @final For my own reasons (i have some ideas), please use SaturableInterface and init method.
     * @param InputManager       $input
     * @param ContainerInterface $container
     */
    final public function __construct(
        InputManager $input,
        ContainerInterface $container
    ) {
        $this->input = $input;
        $this->container = $container;

        foreach ($this->schema as $field => $source) {
            list($source, $origin) = $this->parseSource($field, $source);

            if (!method_exists($input, $source)) {
                throw new FilterException("Undefined source '{$source}'.");
            }

            //Receiving value as result of InputManager method
            $this->setField($field, call_user_func([$input, $source], $origin), true);
        }

        if (method_exists($this, Service::INIT_METHOD)) {
            $method = new \ReflectionMethod($this, Service::INIT_METHOD);
            $method->invokeArgs($this, $container->resolveArguments($method));
        }
    }

    /**
     * Associated InputManager instance. Attention, input manager may not decorate save server
     * request as while request filter constructing.
     *
     * @return InputManager
     */
    public function input()
    {
        return $this->input;
    }

    /**
     * Must populate fields of given entity and return true if entity still valid after this
     * operation. Method must return false is request is not valid by itself.
     *
     * Method might create new set of errors related to population process using setError() method.
     *
     * @param EntityInterface $entity
     * @return bool
     */
    public function populate(EntityInterface $entity)
    {
        if (!$this->isValid()) {
            return false;
        }

        //Populating fields
        $entity->setFields($this);

        if (!$entity->isValid()) {
            foreach ($entity->getErrors() as $name => $error) {
                if (isset($this->schema[$name])) {
                    $this->errors[$name] = $error;
                }
            }

            return false;
        }

        //All good
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors($reset = false)
    {
        //De-mapping
        $errors = [];
        foreach (parent::getErrors($reset) as $field => $errorSet) {
            list(, $origin) = $this->parseSource($field, $this->schema[$field]);

            if ($field == $origin) {
                $errors[$field] = $errorSet;
            } else {
                //Let's recreate original structure
                $this->dotSet($errors, $origin, $errorSet);
            }
        }

        return $errors;
    }

    /**
     * @return object
     */
    public function __debugInfo()
    {
        return (object)[
            'schema' => $this->schema,
            'fields' => $this->getFields(),
            'errors' => $this->getErrors()
        ];
    }

    /**
     * Fetch source name and origin from schema definition.
     *
     * @param string $field
     * @param string $source
     * @return array [$source, $origin]
     */
    private function parseSource($field, $source)
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
    private function dotSet(array &$array, $path, $value)
    {
        $step = explode('.', $path);
        while ($name = array_shift($step)) {
            $array = &$array[$name];
        }

        $array = $value;
    }
}