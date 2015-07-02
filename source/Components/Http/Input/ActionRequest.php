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
     * Request data schema.
     *
     * @var array
     */
    protected $schema = [];

    /**
     * New instance of action request.
     *
     * @param InputManager $input
     */
    public function __construct(InputManager $input)
    {
    }
}