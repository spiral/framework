<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Image;

use Spiral\Components\Files\FileManager;
use Spiral\Core\Component;

/**
 * @property string $objectName
 * @property string $editStatus
 * @property string $urgency
 * @property string $category
 * @property array  $keywords
 * @property string $createdDate
 * @property string $createdTime
 * @property string $originatingProgram
 * @property string $programVersion
 * @property array  $byline
 * @property array  $bylineTitle
 * @property string $city
 * @property string $subLocation
 * @property string $provinceState
 * @property string $countyCode
 * @property string $country
 * @property string $headline
 * @property string $credit
 * @property string $source
 * @property string $copyright
 * @property array  $contact
 * @property string $caption
 */
class IptcData extends Component
{
    /**
     * IPTC mapping constants.
     */
    const ID         = 0;
    const REPEATABLE = 1;
    const LENGTH     = 2;

    /**
     * Known IPTC fields associations input and output conversions (most popular fields included).
     * Format: data id, repeatable, max item length.
     *
     * @invisible
     * @link http://www.iptc.org/std/IIM/4.1/specification/IIMV4.1.pdf
     * @var array
     */
    public $mapping = array(
        'objectName'         => array('2#005', false, 64),
        'editStatus'         => array('2#007', false, 64),
        'urgency'            => array('2#010', false, 1),
        'category'           => array('2#015', false, 3),
        'keywords'           => array('2#025', true, 64),
        'createdDate'        => array('2#055', false, 8),
        'createdTime'        => array('2#060', false, 11),
        'originatingProgram' => array('2#065', false, 32),
        'programVersion'     => array('2#070', false, 10),
        'byline'             => array('2#080', true, 32),
        'bylineTitle'        => array('2#085', true, 32),
        'city'               => array('2#090', false, 32),
        'subLocation'        => array('2#092', false, 32),
        'provinceState'      => array('2#095', false, 32),
        'countyCode'         => array('2#100', false, 3),
        'country'            => array('2#101', false, 64),
        'headline'           => array('2#105', false, 256),
        'credit'             => array('2#110', false, 32),
        'source'             => array('2#115', false, 32),
        'copyright'          => array('2#116', false, 128),
        'contact'            => array('2#118', true, 128),
        'caption'            => array('2#120', false, 2000)
    );

    /**
     * Currently open image filename, all IPTC data will be written to this file, make sure image
     * wasn't moved at moment of writing IPTC.
     *
     * @var string
     */
    protected $filename = '';

    /**
     * Imageinfo fetched from image using getimagesize().
     *
     * @invisible
     * @var array
     */
    protected $imageinfo = array();

    /**
     * Parsed but unmapped IPTC data.
     *
     * @var array
     */
    protected $APP13 = array();

    /**
     * Parsed and mapped IPTC data.
     *
     * @var array
     */
    protected $IPTC = null;

    /**
     * Flag that IPTC data were updated.
     *
     * @var true
     */
    protected $updated = false;

    /**
     * FileManager component.
     *
     * @var FileManager
     */
    protected $file = null;

    /**
     * IPTC metadata embedded in images are often referred to as "IPTC headers", and can be easily
     * encoded and decoded by most popular photo editing software. Usually contains software identified,
     * picture title, keywords and etc. For more complex image metadata manipulations consider
     * using: http://www.sno.phy.queensu.ca/~phil/exiftool/
     *
     * @link http://www.iptc.org/std/IIM/4.1/specification/IIMV4.1.pdf
     * @param string      $filename  Linked image filename.
     * @param array       $imageinfo Imageinfo fetched using getimagesize().
     * @param FileManager $file      File component.
     */
    public function __construct($filename, array $imageinfo, FileManager $file)
    {
        $this->filename = $filename;
        $this->imageinfo = $imageinfo;
        $this->file = $file;
    }

    /**
     * All supported IPTC field names.
     *
     * @return array
     */
    public function getSupportedFields()
    {
        return array_keys($this->mapping);
    }

    /**
     * Parse IPTC fields if not parsed already.
     */
    protected function parseIPTC()
    {
        if ($this->IPTC !== null)
        {
            return;
        }

        if (!isset($this->imageinfo['APP13']))
        {
            $this->IPTC = array();

            return;
        }

        $this->APP13 = iptcparse($this->imageinfo['APP13']);

        //Mapping fields
        foreach ($this->mapping as $name => $definition)
        {
            if (isset($this->APP13[$definition[self::ID]]))
            {
                $this->IPTC[$name] = $this->APP13[$definition[self::ID]];
                if (is_array($this->IPTC[$name]) && !$definition[self::REPEATABLE])
                {
                    $this->IPTC[$name] = join('', $this->IPTC[$name]);
                }
            }
        }
    }

    /**
     * Get IPTC field value.
     *
     * @param string $name IPTC field name.
     * @return string|array
     * @throws ImageException
     */
    public function &get($name)
    {
        $this->parseIPTC();

        if (!isset($this->mapping[$name]))
        {
            throw new ImageException("Unknown IPTC field '{$name}'.");
        }

        if (!isset($this->IPTC[$name]))
        {
            $this->IPTC[$name] = $this->mapping[$name][self::REPEATABLE] ? array() : '';
        }

        return $this->IPTC[$name];
    }

    /**
     * Get IPTC field value.
     *
     * @param string $name
     * @return array|string
     * @throws ImageException
     */
    public function &__get($name)
    {
        return $this->get($name);
    }

    /**
     * Get all parsed IPTC fields.
     *
     * @return array
     */
    public function getAll()
    {
        $this->parseIPTC();

        return $this->IPTC;
    }

    /**
     * Set IPTC field value, value should match field type (repeatable or not) and be shorter than
     * max length. ImageIPTC will automatically ensure this both parameters (length and type).
     *
     * @param string $name IPTC field name.
     * @param mixed  $value
     * @return static
     * @throws ImageException
     */
    public function set($name, $value)
    {
        $this->parseIPTC();

        if (!isset($this->mapping[$name]))
        {
            throw new ImageException("Unknown IPTC field '{$name}'.");
        }

        $mapping = $this->mapping[$name];
        $this->updated = true;

        if ($mapping[self::REPEATABLE] && !is_array($value))
        {
            $value = array($value);
        }

        if (!$mapping[self::REPEATABLE] && is_array($value))
        {
            $value = join('', $value);
        }

        if (is_array($value))
        {
            foreach ($value as &$item)
            {
                $item = substr($item, 0, $mapping[self::LENGTH]);
                unset($item);
            }
        }
        else
        {
            $value = substr($value, 0, $mapping[self::LENGTH]);
        }

        $this->IPTC[$name] = $value;

        return $this;
    }

    /**
     * Set IPTC field value, value should match field type (repeatable or not) and be shorter than
     * max length. ImageIPTC will automatically ensure this both parameters (length and type).
     *
     * @param string $name IPTC field name.
     * @param mixed  $value
     * @throws ImageException
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Pack IPTC data to be written using iptcembed().
     *
     * @link http://php.net/manual/en/function.iptcembed.php
     * @return string
     */
    protected function packIPTC()
    {
        $packedIPTC = '';

        foreach ($this->IPTC as $name => $value)
        {
            //__get by reference makes us do it.
            $this->APP13[$this->mapping[$name][self::ID]] = is_array($value) ? $value : array($value);
        }

        //UTF-8 Encoding
        $this->APP13['1#090'] = array(chr(0x1b) . chr(0x25) . chr(0x47));
        foreach ($this->APP13 as $name => $value)
        {
            list($section, $id) = explode('#', $name);

            foreach ($value as $item)
            {
                $packedIPTC .= $this->makeTag((int)$section, (int)$id, $item);
            }
        }

        return $packedIPTC;
    }

    /**
     * Create IPTC tag.
     *
     * @link http://php.net/manual/en/function.iptcembed.php
     * @param int    $section
     * @param int    $id
     * @param string $value
     * @return string
     */
    protected function makeTag($section, $id, $value)
    {
        $length = strlen($value);

        $packed = chr(0x1C) . chr($section) . chr((int)$id);
        if ($length < 0x8000)
        {
            $packed .= chr($length >> 8) . chr($length & 0xFF);
        }
        else
        {
            $packed .= chr(0x80) .
                chr(0x04) .
                chr(($length >> 24) & 0xFF) .
                chr(($length >> 16) & 0xFF) .
                chr(($length >> 8) & 0xFF) .
                chr($length & 0xFF);
        }

        return $packed . $value;
    }


    /**
     * Save IPTC data to linked filename.
     *
     * @return bool
     */
    public function save()
    {
        if (!$this->updated)
        {
            return true;
        }

        if (!$content = iptcembed($this->packIPTC(), $this->filename))
        {
            return false;
        }

        $this->file->write($this->filename, $content);
        $this->updated = false;

        return true;
    }

    /**
     * Simplified data dumper.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->getAll();
    }
}