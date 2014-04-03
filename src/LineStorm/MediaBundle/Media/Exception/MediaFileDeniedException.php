<?php

namespace LineStorm\MediaBundle\Media\Exception;

class MediaFileDeniedException extends \Exception
{
    function __construct($message='This media type is not allowed')
    {
        parent::__construct($message);
    }
}
