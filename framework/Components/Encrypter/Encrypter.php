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
use Spiral\Core\Core;

class Encrypter extends Component
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait, Component\ConfigurableTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = 'encrypter';

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
    protected $cipher = MCRYPT_RIJNDAEL_128;

    /**
     * One of the MCRYPT_MODE_modename constants, or one of the following strings: "ecb", "cbc", "cfb", "ofb", "nofb" or
     * "stream".
     *
     * @var string
     */
    protected $mode = MCRYPT_MODE_CBC;

    /**
     * The key with which the data will be encrypted. Default application key should be defined in encrypter configuration
     * and can not be empty.
     *
     * @var string
     */
    protected $key = '';

    /**
     * The source of the IV. The source can be MCRYPT_RAND (system random number generator), MCRYPT_DEV_RANDOM (read data
     * from /dev/random) and MCRYPT_DEV_URANDOM (read data from /dev/urandom).
     *
     * Prior to 5.3.0, MCRYPT_RAND was the only one supported on Windows.
     *
     * @var int|null
     */
    protected $ivSource = null;

    /**
     * New encrypter component.
     *
     * @param Core $core
     * @throws EncrypterException
     */
    public function __construct(Core $core)
    {
        $this->config = $core->loadConfig('encrypter');

        if (isset($this->config['cipher']))
        {
            $this->cipher = $this->config['cipher'];
        }

        if (isset($this->config['mode']))
        {
            $this->mode = $this->config['mode'];
        }

        if (!function_exists('openssl_random_pseudo_bytes'))
        {
            throw new EncrypterException('OpenSSL extension is required to work securely.');
        }

        $this->setKey($this->config['key']);

        defined('MCRYPT_DEV_URANDOM') && ($this->ivSource = MCRYPT_DEV_URANDOM);
        defined('MCRYPT_DEV_RANDOM') && ($this->ivSource = MCRYPT_DEV_RANDOM);

        if (!$this->ivSource)
        {
            mt_srand();
            $this->ivSource = MCRYPT_RAND;
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
     * Set the encryption cipher.
     *
     * @param  string $cipher
     * @return static
     */
    public function setCipher($cipher)
    {
        $this->cipher = $cipher;

        return $this;
    }

    /**
     * Set the encryption mode.
     *
     * @param  string $mode
     * @return static
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

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
        $this->setCipher($this->config['cipher']);
        $this->setMode($this->config['mode']);

        return $this;
    }

    /**
     * Generate a pseudo-random string of bytes.
     *
     * @link http://php.net/manual/en/function.openssl-random-pseudo-bytes.php
     * @param int  $length   Required string length (count bytes).
     * @param bool $passWeak Do not throw an exception if result is "weak". Not recommended.
     * @param bool $base64   If true string will be converted to base64 to prevent unprintable characters.
     * @return string
     * @throws EncrypterException
     */
    public function random($length, $passWeak = false, $base64 = true)
    {
        if ($length < 1)
        {
            throw new EncrypterException("Random string length should be at least 1 byte long.");
        }

        if (!$result = openssl_random_pseudo_bytes($length, $cryptoStrong))
        {
            throw new EncrypterException("Unable to generate pseudo-random string with {$length} length.");
        }

        if (!$passWeak && !$cryptoStrong)
        {
            throw new EncrypterException("Weak random result received.");
        }

        if ($base64)
        {
            return substr(base64_encode($result), 0, $length);
        }

        return $result;
    }

    /**
     * Get string signature for current application key, signature can be used to verify that string is valid without
     * encrypting/decrypting it.
     *
     * @param string $string Encrypted string.
     * @param string $salt   String salt.
     * @return string
     */
    public function buildSignature($string, $salt = null)
    {
        return hash_hmac('sha256', $string . ($salt ? ':' . $salt : ''), $this->key);
    }

    /**
     * Creates an initialization vector (IV) from a random source with specified size.
     *
     * @link http://php.net/manual/en/function.mcrypt-create-iv.php
     * @return string
     */
    protected function createIV()
    {
        return mcrypt_create_iv(mcrypt_get_iv_size($this->cipher, $this->mode), $this->ivSource);
    }

    /**
     * Encrypt given data (any serializable) using current encryption cipher, mode and key. Data will be base64 encoded
     * and signed. Use additional parameter to make output URL friendly. Result will be encrypted string packed with
     * signature and vector.
     *
     * @link http://stackoverflow.com/questions/1374753/passing-base64-encoded-strings-in-url
     * @param mixed $data    Data to be encrypted.
     * @param bool  $urlSafe Apply patch to base64 data to make it URL friendly.
     * @return string
     * @throws EncrypterException
     */
    public function encrypt($data, $urlSafe = true)
    {
        if (!$this->key)
        {
            throw new EncrypterException("Encryption key should not be empty.");
        }

        $vector = $this->createIV();

        $data = base64_encode(mcrypt_encrypt(
            $this->cipher,
            $this->key,
            $this->addPKCS7(serialize($data)),
            $this->mode,
            $vector
        ));

        $vector = base64_encode($vector);
        $result = json_encode(array(
            self::IV        => $vector,
            self::DATA      => $data,
            self::SIGNATURE => $this->buildSignature($data, $vector)
        ));

        $result = base64_encode($result);

        if ($urlSafe)
        {
            //http://stackoverflow.com/questions/1374753/passing-base64-encoded-strings-in-url
            return str_replace(array('+', '/', '='), array('-', '_', '~'), $result);
        }

        return $result;
    }

    /**
     * Decrypt previously data, verify signature and return it. All Encryption options should be identical to values used
     * during encryption.
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
            $packed = base64_decode(str_replace(array('-', '_', '~'), array('+', '/', '='), $packed));
            $packed = json_decode($packed, true);
        }
        catch (\ErrorException $exception)
        {
            throw new DecryptionException("Unable to unpack provided data.");
        }

        if (!is_array($packed) || !isset($packed[self::IV]) || !isset($packed[self::DATA]) || !isset($packed[self::SIGNATURE]))
        {
            throw new DecryptionException("Unable to unpack provided data.");
        }

        if (!$packed[self::SIGNATURE])
        {
            throw new DecryptionException("Encrypted data does not have signature.");
        }

        //Verifying signature
        if ($packed[self::SIGNATURE] !== $this->buildSignature($packed[self::DATA], $packed[self::IV]))
        {
            throw new DecryptionException("Encrypted data does not have valid signature.");
        }

        try
        {
            $data = mcrypt_decrypt(
                $this->cipher,
                $this->key,
                base64_decode($packed[self::DATA]),
                $this->mode,
                base64_decode($packed[self::IV])
            );

            return unserialize($this->removePKCS7($data));
        }
        catch (\ErrorException $exception)
        {
            throw new DecryptionException($exception->getMessage());
        }
    }

    /**
     * Data padding mcrypt always pads data will the null character but .NET has two padding modes: "Zeros" and "PKCS7",
     * zeros is identical to the mcrypt scheme, but PKCS7 is the default. PKCS7 isn't much more complex, though: instead
     * of nulls, it appends the total number of padding bytes (which means, for 3DES, it can be a value from 0x01 to 0x07)
     *
     * @link http://php.net/manual/en/function.mcrypt-encrypt.php
     * @param string $string String to be encoded.
     * @return string
     */
    protected function addPKCS7($string)
    {
        $blockSize = mcrypt_get_block_size($this->cipher, $this->mode);
        $padding = $blockSize - (strlen($string) % $blockSize);

        return $string . str_repeat(chr($padding), $padding);
    }

    /**
     * Data padding mcrypt always pads data will the null character but .NET has two padding modes: "Zeros" and "PKCS7",
     * zeros is identical to the mcrypt scheme, but PKCS7 is the default. PKCS7 isn't much more complex, though: instead
     * of nulls, it appends the total number of padding bytes (which means, for 3DES, it can be a value from 0x01 to 0x07)
     *
     * @link http://php.net/manual/en/function.mcrypt-encrypt.php
     * @param string $string Decrypted string.
     * @return string
     */
    protected function removePKCS7($string)
    {
        $length = strlen($string);
        $padding = ord($string[$length - 1]);

        //Expected length without padding
        $unpadded = $length - $padding;

        //Is padding presented?s
        if (substr($string, $unpadded) == str_repeat(substr($string, -1), $padding))
        {
            //Removing padding
            return substr($string, 0, $unpadded);
        }

        //No PKCS7 paddings were attached
        return $string;
    }
}