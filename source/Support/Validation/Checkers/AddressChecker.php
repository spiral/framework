<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Validation\Checkers;

use Spiral\Support\Validation\Checker;

class AddressChecker extends Checker
{
    /**
     * Set of default error messages associated with their check methods organized by method name.
     * Will be returned by the checker to replace the default validator message. Can have placeholders
     * for interpolation.
     *
     * @var array
     */
    protected $messages = [
        "email"     => "[[Field '{field}' is not a valid email address.]]",
        "fullEmail" => "[[Field '{field}' is not a valid email address.]]",
        "url"       => "[[Field '{field}' is not a valid URL.]]"
    ];

    /**
     * Email address validation. An internal PHP function filter_var() will be used.
     *
     * @link http://www.ietf.org/rfc/rfc2822.txt
     * @param string $email Email to validate.
     * @return bool
     */
    public function email($email)
    {
        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Will validate email with included recipient name in <> braces. Do not use this validation
     * function outside of the admin panels.
     *
     * @param string $email Full email address which will be validated.
     * @return bool
     */
    public function fullEmail($email)
    {
        if (is_string($email) && preg_match('/<([^>]+)>$/i', $email, $matches))
        {
            $email = $matches[1];
        }

        return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validating HTTP or HTTPS web URLs using PHP filter_var() function, RCF 2396 compliant. If the
     * second argument is false, the system will force http:// protocol to address before any validation.
     *
     * @link http://www.faqs.org/rfcs/rfc2396.html
     * @param string $url             URL which will be validated.
     * @param bool   $requireProtocol If true, this will require having a protocol definition.
     * @return bool
     */
    public function url($url, $requireProtocol = true)
    {
        if (!$requireProtocol && stripos($url, 'http://') === false && stripos($url, 'https://') === false)
        {
            $url = 'http://' . $url;
        }

        return (bool)filter_var($url, FILTER_VALIDATE_URL);
    }
}