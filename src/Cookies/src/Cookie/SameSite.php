<?php

declare(strict_types=1);

namespace Spiral\Cookies\Cookie;

class SameSite
{
    public const STRICT = 'Strict';
    public const LAX    = 'Lax';
    public const NONE   = 'None';

    private const VALUES  = [self::STRICT, self::LAX, self::NONE];
    private const DEFAULT = self::LAX;

    /** @var string|null */
    private $sameSite;

    public function __construct(?string $sameSite = null, bool $secure = false)
    {
        $this->sameSite = $this->defineValue($sameSite, $secure);
    }

    public function get(): ?string
    {
        return $this->sameSite;
    }

    /**
     * @param string|null $sameSite
     * @param bool        $secure
     * @return string|null
     */
    private function defineValue(?string $sameSite, bool $secure): ?string
    {
        if ($sameSite === null) {
            return null;
        }

        $sameSite = ucfirst(strtolower($sameSite));
        if (!in_array($sameSite, self::VALUES, true)) {
            return null;
        }

        return ($sameSite === self::NONE && !$secure) ? self::DEFAULT : $sameSite;
    }
}
