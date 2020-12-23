<?php

declare(strict_types=1);

namespace Spiral\Domain;

class Permission
{
    /** @var bool */
    public $ok = false;

    /** @var string */
    public $permission;

    /** @var int */
    public $code;

    /** @var string */
    public $message;

    protected function __construct()
    {
    }

    public static function failed(): self
    {
        return new self();
    }

    public static function ok(string $permission, int $code, string $message): self
    {
        $self = new self();
        $self->ok = true;
        $self->permission = $permission;
        $self->code = $code;
        $self->message = $message;

        return $self;
    }
}
