<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Encrypter;

use Spiral\Core\Component;
use Spiral\Core\ConfiguratorInterface;

class Encrypter extends Component
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait, Component\ConfigurableTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * Keys to use in packed data.
     */
    const IV        = 'a';
    const DATA      = 'b';
    const SIGNATURE = 'c';

    /**
     * One of the MCRYPT_ciphername constants, or the name of the algorithm as string.
     *
     * @var string
     */
    protected $method = 'aes-256-cbc';

    /**
     * The key with which the data will be encrypted. Default application key should be defined in
     * encrypter configuration and can not be empty.
     *
     * @var string
     */
    protected $key = '';

    /**
     * New encrypter component.
     *
     * @param ConfiguratorInterface $configurator
     * @throws EncrypterException
     */
    public function __construct(ConfiguratorInterface $configurator)
    {
        $this->config = $configurator->getConfig('encrypter');

        $this->setKey($this->config['key']);
        if (!empty($this->config['method']))
        {
            $this->method = $this->config['method'];
        }
    }

    /**
     * Set the encryption key.
     *
     * @param  string $key
     * @return static
     * @throws EncrypterException
     */
    public function setKey($key)
    {
        $this->key = (string)$key;

        return $this;
    }

    /**
     * Get current encrypter key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the encryption cipher.
     *
     * @param  string $method
     * @return static
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Restore encryption values specified in configuration,
     * key, mode and cipher will be altered.
     *
     * @return static
     * @throws EncrypterException
     */
    public function restoreDefaults()
    {
        $this->setKey($this->config['key']);
        $this->setMethod($this->config['cipher']);

        return $this;
    }

    /**
     * Generate a pseudo-random string of bytes.
     *
     * @link http://php.net/manual/en/function.openssl-random-pseudo-bytes.php
     * @param int  $length   Required string length (count bytes).
     * @param bool $passWeak Do not throw an exception if result is "weak". Not recommended.
     * @return string
     * @throws EncrypterException
     */
    public function random($length, $passWeak = false)
    {
        if ($length < 1)
        {
            throw new EncrypterException("Random string length should be at least 1 byte long.");
        }

        if (!$result = openssl_random_pseudo_bytes($length, $cryptoStrong))
        {
            throw new EncrypterException(
                "Unable to generate pseudo-random string with {$length} length."
            );
        }

        if (!$passWeak && !$cryptoStrong)
        {
            throw new EncrypterException("Weak random result received.");
        }

        return $result;
    }

    /**
     * Get string signature for current application key, signature can be used to verify that string
     * is valid without encrypting/decrypting it.
     *
     * @param string $string Encrypted string.
     * @param string $salt   String salt.
     * @return string
     */
    public function makeSignature($string, $salt = null)
    {
        return hash_hmac('sha256', $string . ($salt ? ':' . $salt : ''), $this->key);
    }

    /**
     * Creates an initialization vector (IV) from a random source with specified size.
     *
     * @link http://php.net/manual/en/function.mcrypt-create-iv.php
     * @param int $length
     * @return string
     */
    protected function createIV($length = 16)
    {
        return $length ? $this->random($length, true, false) : '';
    }

    /**
     * Encrypt given data (any serializable) using current encryption cipher, mode and key. Data will
     * be base64 encoded and signed. Use additional parameter to make output URL friendly. Result will
     * be encrypted string packed with signature and vector.
     *
     * @link http://stackoverflow.com/questions/1374753/passing-base64-encoded-strings-in-url
     * @param mixed $data Data to be encrypted.
     * @return string
     * @throws EncrypterException
     */
    public function encrypt($data)
    {
        if (empty($this->key))
        {
            throw new EncrypterException("Encryption key should not be empty.");
        }

        $vector = $this->createIV(openssl_cipher_iv_length($this->method));

        $encrypted = openssl_encrypt(
            serialize($data),
            $this->method,
            $this->key,
            false,
            $vector
        );

        $result = json_encode(array(
            self::IV        => ($vector = bin2hex($vector)),
            self::DATA      => $encrypted,
            self::SIGNATURE => $this->makeSignature($encrypted, $vector)
        ));

        return base64_encode($result);
    }

    /**
     * Decrypt previously data, verify signature and return it. All Encryption options should be
     * identical to values used during encryption.
     *
     * @link http://php.net/manual/en/function.mcrypt-decrypt.php
     * @param string $packed Packed string generated by Encrypter->encrypt().
     * @return mixed
     * @throws DecryptionException
     */
    public function decrypt($packed)
    {
        try
        {
            $packed = json_decode(base64_decode($packed), true);

            if (empty($packed) || !is_array($packed))
            {
                throw new DecryptionException("Invalid dataset.");
            }

            assert(!empty($packed[self::IV]));
            assert(!empty($packed[self::DATA]));
            assert(!empty($packed[self::SIGNATURE]));
        }
        catch (\ErrorException $exception)
        {
            throw new DecryptionException("Unable to unpack provided data.");
        }

        //Verifying signature
        if ($packed[self::SIGNATURE] !== $this->makeSignature($packed[self::DATA], $packed[self::IV]))
        {
            throw new DecryptionException("Encrypted data does not have valid signature.");
        }

        try
        {
            $decrypted = openssl_decrypt(
                base64_decode($packed[self::DATA]),
                $this->method,
                $this->key,
                true,
                hex2bin($packed[self::IV])
            );

            return unserialize($decrypted);
        }
        catch (\ErrorException $exception)
        {
            throw new DecryptionException($exception->getMessage());
        }
    }
}