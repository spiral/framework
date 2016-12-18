<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Models;

use Spiral\Core\Traits\SaturateTrait;
use Spiral\Validation\ValidatorInterface;

/**
 * Provides ability to validate mocked data.
 */
class ValidatesEntity extends StaticDateEntity
{
    use SaturateTrait;

    /**
     * Validation rules compatible with ValidatorInterface.
     */
    const VALIDATES = [];

    /**
     * @var ValidatorInterface
     */
    private $validator = null;

    /**
     * @param array              $fields
     * @param ValidatorInterface $validator
     */
    public function __construct(array $fields, ValidatorInterface $validator = null)
    {
        parent::__construct($fields);
        $this->validator = $this->saturate($validator, ValidatorInterface::class);
    }
}