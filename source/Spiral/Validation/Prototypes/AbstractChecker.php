<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Validation\Prototypes;

use Interop\Container\ContainerInterface;
use Spiral\Core\Component;
use Spiral\Core\Exceptions\ScopeException;
use Spiral\Core\Traits\SaturateTrait;
use Spiral\Translator\Traits\TranslatorTrait;
use Spiral\Validation\CheckerInterface;
use Spiral\Validation\Exceptions\CheckerException;
use Spiral\Validation\ValidatorInterface;

/**
 * Checkers used to group set of validation rules under one roof.
 *
 * Depends on container due it's usual implementation provides env specific operations in some cases
 */
abstract class AbstractChecker extends Component implements CheckerInterface
{
    use TranslatorTrait, SaturateTrait;

    /**
     * Default error messages associated with checker method by name.
     *
     * @var array
     */
    const MESSAGES = [];

    /**
     * @var ValidatorInterface
     */
    private $validator = null;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container Needed for translations and other things, saturated
     *
     * @throws ScopeException
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $this->saturate($container, ContainerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function check(
        string $method,
        $value,
        array $arguments = []
    ) {
        array_unshift($arguments, $value);

        return call_user_func_array([$this, $method], $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage(string $method): string
    {
        $messages = static::MESSAGES;
        if (isset($messages[$method])) {
            return $this->say($messages[$method]);
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function withValidator(ValidatorInterface $validator): CheckerInterface
    {
        $checker = clone $this;
        $checker->validator = $validator;

        return $checker;
    }

    /**
     * Currently active validator instance.
     *
     * @return ValidatorInterface
     */
    protected function getValidator(): ValidatorInterface
    {
        if (empty($this->validator)) {
            throw new CheckerException("Unable to receive associated checker validator");
        }

        return $this->validator;
    }
}