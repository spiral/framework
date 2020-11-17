<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Exception;

use Doctrine\Common\Annotations\AnnotationException;

class AttributeException extends \RuntimeException
{
    /**
     * @var int
     */
    public const ERROR_CODE_SYNTAX = 0x01;

    /**
     * @var int
     */
    public const ERROR_CODE_SEMANTIC = 0x02;

    /**
     * @var int
     */
    public const ERROR_CODE_CREATION = 0x03;

    /**
     * @var int
     */
    public const ERROR_CODE_TYPE = 0x04;

    /**
     * @var string
     */
    private const ERROR_PREFIX_SYNTAX = '[Syntax Error]';

    /**
     * @var string
     */
    private const ERROR_PREFIX_SEMANTIC = '[Semantical Error]';

    /**
     * @var string
     */
    private const ERROR_PREFIX_CREATION = '[Creation Error]';

    /**
     * @var string
     */
    private const ERROR_PREFIX_TYPE = '[Type Error]';

    /**
     * {@inheritDoc}
     */
    final public function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Creates a new {@see AttributeException} describing a syntax error.
     *
     * @see AnnotationException::syntaxError()
     *
     * @param string $message Exception message
     * @return static
     */
    public static function syntaxError(string $message): self
    {
        return new static(self::ERROR_PREFIX_SYNTAX . ' ' . $message, static::ERROR_CODE_SYNTAX);
    }

    /**
     * Creates a new {@see AttributeException} describing a semantical error.
     *
     * @see AnnotationException::semanticalError()
     *
     * @param string $message Exception message
     * @return static
     */
    public static function semanticalError(string $message): self
    {
        return new static(self::ERROR_PREFIX_SEMANTIC . ' ' . $message, static::ERROR_CODE_SEMANTIC);
    }

    /**
     * Creates a new {@see AttributeException} describing an error which
     * occurred during the creation of the attribute.
     *
     * @see AnnotationException::creationError()
     *
     * @param string $message
     * @return static
     */
    public static function creationError(string $message): self
    {
        return new static(self::ERROR_PREFIX_CREATION . ' ' . $message, static::ERROR_CODE_CREATION);
    }

    /**
     * Creates a new {@see AttributeException} describing a type error.
     *
     * @see AnnotationException::typeError()
     *
     * @param string $message
     * @return static
     */
    public static function typeError(string $message): self
    {
        return new static(self::ERROR_PREFIX_TYPE . ' ' . $message, self::ERROR_CODE_TYPE);
    }

    /**
     * @param AnnotationException $e
     * @return static
     */
    public static function fromDoctrine(AnnotationException $e): self
    {
        [$message, $previous] = [$e->getMessage(), $e->getPrevious()];

        switch (true) {
            case \str_starts_with(self::ERROR_PREFIX_SYNTAX, $message):
                return new static($message, self::ERROR_CODE_SYNTAX, $previous);

            case \str_starts_with(self::ERROR_PREFIX_CREATION, $message):
                return new static($message, self::ERROR_CODE_CREATION, $previous);

            case \str_starts_with(self::ERROR_PREFIX_SEMANTIC, $message):
                return new static($message, self::ERROR_CODE_SEMANTIC, $previous);

            case \str_starts_with(self::ERROR_PREFIX_TYPE, $message):
                return new static($message, self::ERROR_CODE_TYPE, $previous);

            default:
                return new static($message, 0, $previous);
        }
    }
}
