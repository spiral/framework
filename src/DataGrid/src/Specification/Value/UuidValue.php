<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Exception\ValueException;
use Spiral\DataGrid\Specification\ValueInterface;

/**
 * UUID strings value with mask validation.
 * @see https://github.com/particle-php/Validator/blob/master/src/Rule/Uuid.php
 */
final class UuidValue implements ValueInterface
{
    /**
     * Compare masks.
     */
    public const VALID = 'valid';
    public const NIL   = 'nil';
    public const V1    = 'v1';
    public const V2    = 'v2';
    public const V3    = 'v3';
    public const V4    = 'v4';
    public const V5    = 'v5';

    /**
     * An array of all validation regex patterns.
     */
    private const PATTERNS = [
        self::VALID => '~^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$~i',
        self::V1    => '~^[0-9a-f]{8}-[0-9a-f]{4}-1[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$~i',
        self::V2    => '~^[0-9a-f]{8}-[0-9a-f]{4}-2[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$~i',
        self::V3    => '~^[0-9a-f]{8}-[0-9a-f]{4}-3[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$~i',
        self::V4    => '~^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$~i',
        self::V5    => '~^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$~i',
    ];

    /**
     * The nil UUID is special form of UUID that is specified to have all 128 bits set to zero.
     * @link http://tools.ietf.org/html/rfc4122#section-4.1.7
     */
    private const NIL_VALUE = '00000000-0000-0000-0000-000000000000';

    /** @var string */
    private $mask;

    /** @var RegexValue */
    private $regex;

    public function __construct(string $mask = self::VALID)
    {
        $this->mask = strtolower($mask);

        if ($this->mask !== self::NIL && !isset(self::PATTERNS[$this->mask])) {
            throw new ValueException('Invalid UUID version mask given. Please choose one of the constants.');
        }

        $this->regex = new RegexValue(self::PATTERNS[$this->mask !== self::NIL ? $this->mask : self::VALID]);
    }

    /**
     * @inheritDoc
     */
    public function accepts($value): bool
    {
        return (is_numeric($value) || is_string($value)) && $this->isValid($this->convert($value));
    }

    /**
     * @inheritDoc
     */
    public function convert($value): string
    {
        return (string)$value;
    }

    private function isValid(string $value): bool
    {
        $uuid = str_replace(['urn:', 'uuid:', '{', '}'], '', $value);

        if ($this->mask === self::NIL) {
            return $value === self::NIL_VALUE;
        }

        return $this->regex->accepts($uuid);
    }
}
