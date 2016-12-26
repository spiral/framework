<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Http\Middlewares;

/**
 * Requires CSRF token to presented in every passed request.
 */
class StrictCsrfFirewall extends CsrfFirewall
{
    const ALLOW_METHODS = [];
}