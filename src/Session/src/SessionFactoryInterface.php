<?php

declare(strict_types=1);

namespace Spiral\Session;

interface SessionFactoryInterface
{
    /**
     * @param string $clientSignature User specific token, does not provide full security but
     *                                     hardens session transfer.
     * @param string|null $id When null - expect php to create session automatically.
     */
    public function initSession(string $clientSignature, string $id = null): SessionInterface;
}
