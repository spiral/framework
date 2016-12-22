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
 * @todo add error message caching
 */
class RequestFilter extends ValidatesEntity
{
    /**
     * Defines request data mapping (input => request property)
     */
    const SCHEMA = [];

    /**
     * @var InputMapper
     */
    private $mapper;

    /**
     * @param InputInterface     $input
     * @param ValidatorInterface $validator
     */
    public function __construct(InputInterface $input = null, ValidatorInterface $validator = null)
    {
        //We are going to populate input fields manually
        parent::__construct([], $validator);

        if (empty(static::SCHEMA)) {
            throw new InputException("Unable to initiate RequestFilter with empty schema");
        }

        $this->mapper = new InputMapper(static::SCHEMA);
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

        foreach ($this->mapper->createValues($input, $validator) as $field => $value) {
            $this->setField($field, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors(): array
    {
        //Making sure that each error point to proper input origin
        return $this->mapper->originateErrors(parent::getErrors());
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'valid'  => $this->isValid(),
            'fields' => $this->getFields(),
            'errors' => $this->getErrors()
        ];
    }
}