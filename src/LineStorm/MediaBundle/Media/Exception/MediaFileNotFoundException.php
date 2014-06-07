<?php

namespace LineStorm\MediaBundle\Media\Exception;

use LineStorm\MediaBundle\Model\Media;

/**
 * Class MediaFileNotFoundException
 *
 * @package LineStorm\MediaBundle\Media\Exception
 * @author  Andy Thorne <contrabandvr@gmail.com>
 */
class MediaFileNotFoundException extends \Exception
{
    /**
     * @param string     $path
     * @param \Exception $e
     */
    function __construct($path, \Exception $e = null)
    {
        parent::__construct("This media file not found: {$path}", null, $e);
    }
}
