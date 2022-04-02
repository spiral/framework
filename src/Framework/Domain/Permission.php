<?php

declare(strict_types=1);

namespace Spiral\Domain;

class Permission
{
    public bool $ok = false;
    public string $permission;
    public int $code;
    public string $message;

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
