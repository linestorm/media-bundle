<?php

namespace LineStorm\MediaBundle\Media\Exception;

/**
 * Class MediaFileDeniedException
 *
 * @package LineStorm\MediaBundle\Media\Exception
 */
class MediaFileDeniedException extends \Exception
{
    /**
     * @param string $mediaType
     */
    function __construct($mediaType)
    {
        parent::__construct("This media type is not allowed: {$mediaType}");
    }
}
