<?php

namespace LineStorm\MediaBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class MediaCategory
 *
 * @package LineStorm\MediaBundle\Model
 */
class MediaCategory
{

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Media[]
     */
    protected $media;

    /**
     * @var MediaCategory
     */
    protected $parent;

    /**
     * @var MediaCategory[]
     */
    protected $children;

    /**
     *
     */
    function __construct()
    {
        $this->children = new ArrayCollection();
        $this->media    = new ArrayCollection();
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
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param Media $child
     */
    public function addChild(Media $child)
    {
        $this->children[] = $child;
    }

    /**
     * @param Media $child
     */
    public function removeChild(Media $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * @return Media[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return Media
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Media $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @param Media $media
     */
    public function addMedia(Media $media)
    {
        $this->media[] = $media;
    }

    /**
     * @param Media $media
     */
    public function removeMedia(Media $media)
    {
        $this->media->removeElement($media);
    }

    /**
     * @return Media[]
     */
    public function getMedia()
    {
        return $this->media;
    }


}
