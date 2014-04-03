<?php

namespace LineStorm\MediaBundle\Media\Exception;

class MediaFileAlreadyExistsException extends \Exception
{
    private $entity;

    function __construct($entity, $message='The media file is already uploaded')
    {
        $this->entity = $entity;
        parent::__construct($message);
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }


}
