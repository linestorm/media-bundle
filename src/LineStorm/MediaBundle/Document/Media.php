<?php

namespace LineStorm\MediaBundle\Document;

/**
 * Class Media
 *
 * @package LineStorm\MediaBundle\Document
 */
class Media
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $nameOriginal;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var string
     */
    protected $alt;

    /**
     * @var string
     */
    protected $credits;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $src;

    /**
     * @var array Array of all api urls
     */
    protected $_api;

    /**
     * @param \LineStorm\MediaBundle\Model\Media $media
     * @param array                              $api
     */
    function __construct(\LineStorm\MediaBundle\Model\Media $media, array $api = array())
    {
        $this->alt          = $media->getAlt();
        $this->credits      = $media->getCredits();
        $this->description  = $media->getDescription();
        $this->hash         = $media->getHash();
        $this->id           = $media->getId();
        $this->name         = $media->getName();
        $this->nameOriginal = $media->getNameOriginal();
        $this->src          = $media->getSrc();
        $this->title        = $media->getTitle();

        $this->_api         = $api;
    }

    /**
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * @return string
     */
    public function getCredits()
    {
        return $this->credits;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNameOriginal()
    {
        return $this->nameOriginal;
    }

    /**
     * @return string
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getApi()
    {
        return $this->_api;
    }

} 
