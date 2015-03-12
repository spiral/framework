<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Generators;

use Spiral\Core\Component;

class Binary extends Component
{
    /**
     * Predefined file modes, Binary reader/writer will work only in binary modes, hoverer it can
     * append content to already created file.
     */
    const MODE_WRITE  = 'wb';
    const MODE_APPEND = 'ab';
    const MODE_READ   = 'rb';

    /**
     * Select file access mode, based on this mode decision to read or write will be made.
     *
     * @var string
     */
    protected $mode = self::MODE_WRITE;

    /**
     * Context filename where data will be written or reader from. File will be created automatically
     * on object construction.
     *
     * @var bool|string
     */
    protected $filename = false;

    /**
     * File or zipped stream resources.
     *
     * @var bool|resource
     */
    protected $handler = false;

    /**
     * If enabled file will be processed using streamed gzip encoding, should should make output
     * dramatically smaller.
     *
     * @var bool
     */
    protected $compress = false;

    /**
     * Create new Binary instance. Binary generally used to transfer or store big amount of data in
     * plain file, where data structure is undefined and database is not required. Same class can
     * read and write binary files. Last construction option specifies compressing level, if enabled
     * (values 1-9) file will be written using gzip stream. You have to provide compress option while
     * reading compressed files.
     *
     * @param string $filename Local filename or URL (if allowed by PHP).
     * @param string $mode     Read or write mode, values supported - wb, rb, ab. Write by default.
     * @param bool   $compress Compress level (1-9 or false), disabled by default.
     * @throws \Exception
     */
    public function __construct($filename, $mode = self::MODE_WRITE, $compress = false)
    {
        $this->filename = $filename;
        $this->compress = $compress;
        $this->mode = $mode;

        $this->handler = $this->compress
            ? gzopen($this->filename, $this->mode)
            : fopen($this->filename, $this->mode);

        if (empty($this->handler))
        {
            throw new \Exception('Error during opening/creating file.');
        }
    }

    /**
     * Write or append data to open file handler. This function can be used only if append or write
     * mode selected. Data should be either binary or string blob. To encode more complex structures
     * use json_encode or serialize functions. Every data chunk will be prepended with length value
     * which will allow Binary class read exactly same amount of data was written to file.
     *
     * @param string $data Data blob.
     * @return int
     * @throws \Exception
     */
    public function write($data)
    {
        if ($this->mode != self::MODE_WRITE && $this->mode != self::MODE_APPEND)
        {
            throw new \RuntimeException('Unable to write data, read mode is set.');
        }

        if (empty($this->handler))
        {
            throw new \RuntimeException('Unable to add data to file, no resource available.');
        }

        $packedLength = pack('L', strlen($data));

        if (!(bool)$this->compress)
        {
            return gzwrite($this->handler, $packedLength . $data);
        }

        return fwrite($this->handler, $packedLength . $data);
    }

    /**
     * Read one portion of data in binary file. This function can be used only if read select.
     * Resulted data will always be represented as string (blob) and can be unpacked with traditional
     * methods like json_decode or unserialize. Make sure compress option is enabled if original file
     * was created using gzip stream. There is no memory limits on how big binary file can be, hoverer
     * one data portion should fit to PHP memory.
     *
     * @return string
     * @throws \Exception
     */
    public function read()
    {
        if ($this->mode != self::MODE_READ)
        {
            throw new \RuntimeException('Unable to read data, write/append mode is set.');
        }

        if (empty($this->handler))
        {
            throw new \RuntimeException('Unable to read data from file, no resource available.');
        }

        if (!(bool)$this->compress)
        {
            $length = gzread($this->handler, 4);
        }
        else
        {
            $length = fread($this->handler, 4);
        }

        if (empty($length))
        {
            return null;
        }

        if (strlen($length) != 4)
        {
            throw new \Exception('Unable to read length bytes, data is corrupted.');
        }

        $length = unpack('L', $length);

        if ((bool)$this->compress)
        {
            return gzread($this->handler, $length[1]);
        }
        else
        {
            return fread($this->handler, $length[1]);
        }
    }

    /**
     * Close file or gzip stream. Required to be called to ensure that all data was written correctly.
     */
    public function close()
    {
        if (!empty($this->handler))
        {
            if ((bool)$this->compress)
            {
                gzclose($this->handler);
            }
            else
            {
                fclose($this->handler);
            }
        }

        $this->handler = false;
    }

    /**
     * Destructing object and cleaning memory.
     */
    public function __destruct()
    {
        $this->close();
    }
}