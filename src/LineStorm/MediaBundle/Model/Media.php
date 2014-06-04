<?php

namespace LineStorm\MediaBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Media
 * @package LineStorm\MediaBundle\Model
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
     * @var MediaCategory
     */
    protected $category;

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
    protected $src;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var UserInterface
     */
    protected $uploader;

    /**
     * @var Media
     */
    protected $parent;

    /**
     * @var Media[]
     */
    protected $children;

    /**
     *
     */
    function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param MediaCategory $category
     */
    public function setCategory(MediaCategory $category)
    {
        $this->category = $category;
    }

    /**
     * @return MediaCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }


    /**
     * @param string $alt
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;
    }

    /**
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * @param string $credits
     */
    public function setCredits($credits)
    {
        $this->credits = $credits;
    }

    /**
     * @return string
     */
    public function getCredits()
    {
        return $this->credits;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $nameOriginal
     */
    public function setNameOriginal($nameOriginal)
    {
        $this->nameOriginal = $nameOriginal;
    }

    /**
     * @return string
     */
    public function getNameOriginal()
    {
        return $this->nameOriginal;
    }

    /**
     * @param string $src
     */
    public function setSrc($src)
    {
        $this->src = $src;
    }

    /**
     * @return string
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * @param UserInterface $uploader
     */
    public function setUploader(UserInterface $uploader)
    {
        $this->uploader = $uploader;
    }

    /**
     * @return UserInterface
     */
    public function getUploader()
    {
        return $this->uploader;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
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
     * @param Media $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return Media
     */
    public function getParent()
    {
        return $this->parent;
    }

}
