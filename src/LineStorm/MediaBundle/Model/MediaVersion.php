<?php

namespace LineStorm\MediaBundle\Model;

/**
 * Class MediaVersion
 *
 * @package LineStorm\MediaBundle\Model
 * @author  Andy Thorne <contrabandvr@gmail.com>
 */
class MediaVersion
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var MediaResizeProfile
     */
    protected $resizeProfile;

    /**
     * @var Media
     */
    protected $media;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $src;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Media $media
     */
    public function setMedia(Media $media)
    {
        $this->media = $media;
    }

    /**
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
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
     * @param MediaResizeProfile $resizeProfile
     */
    public function setResizeProfile(MediaResizeProfile $resizeProfile)
    {
        $this->resizeProfile = $resizeProfile;
    }

    /**
     * @return MediaResizeProfile
     */
    public function getResizeProfile()
    {
        return $this->resizeProfile;
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
     * Returns the image height
     *
     * @return int
     */
    public function getHeight()
    {
        if($this->path)
        {
            $size = getimagesize($this->path);
            return $size[1];
        }

        return 0;
    }

    /**
     * Returns the image width
     *
     * @return int
     */
    public function getWidth()
    {
        if($this->path)
        {
            $size = getimagesize($this->path);
            return $size[0];
        }

        return 0;
    }

} 
