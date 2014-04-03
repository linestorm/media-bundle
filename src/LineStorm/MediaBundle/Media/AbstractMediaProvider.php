<?php

namespace LineStorm\MediaBundle\Media;


abstract class AbstractMediaProvider
{
    protected $id;

    protected $class;

    public function getId()
    {
        return $this->id;
    }

    public function getEntityClass()
    {
        return $this->class;
    }
} 
