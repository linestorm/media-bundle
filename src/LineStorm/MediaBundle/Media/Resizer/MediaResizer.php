<?php

namespace LineStorm\MediaBundle\Media\Resizer;

use Doctrine\ORM\EntityManager;
use LineStorm\MediaBundle\Model\Media;

/**
 * Class to Resize Media
 *
 * Class MediaResizer
 *
 * @package LineStorm\MediaBundle\Media
 */
class MediaResizer
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var int
     */
    protected $x;

    /**
     * @var int
     */
    protected $y;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param string        $id
     * @param EntityManager $em
     * @param int           $x
     * @param int           $y
     */
    function __construct($id, EntityManager $em, $x = 0, $y = 0)
    {
        $this->id = $id;
        $this->em = $em;
        $this->x  = $x < 0 ? 0 : (int)$x;
        $this->y  = $y < 0 ? 0 : (int)$y;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return mixed
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Check if the X value is inherited
     *
     * @return bool
     */
    public function isXInherited()
    {
        return ($this->x === 0);
    }

    /**
     * Check if the Y value is inherited
     *
     * @return bool
     */
    public function isYInherited()
    {
        return ($this->y === 0);
    }

    /**
     * Check if a media object fits the size profile
     *
     * @param Media $media
     *
     * @return bool
     */
    public function fits(Media $media)
    {
        $fits = true;
        $img  = @getimagesize($media->getPath());

        if($img === false)
            return false;

        if(!$this->isXInherited())
        {
            if($img[0] !== $this->x)
            {
                $fits = false;
            }
        }

        if(!$this->isYInherited())
        {
            if($img[1] !== $this->y)
            {
                $fits = false;
            }
        }

        return $fits;
    }

} 
