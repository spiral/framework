<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Input;

use Psr\Http\Message\UploadedFileInterface;

/**
 * Type hinting.
 *
 * @method UploadedFileInterface|null get($name, $default = null)
 * @method UploadedFileInterface[] all()
 * @method UploadedFileInterface[] fetch(array $keys, $fill = false, $filler = null)
 * @method \ArrayIterator|UploadedFileInterface[] getIterator()
 */
class FileBag extends InputBag
{

}