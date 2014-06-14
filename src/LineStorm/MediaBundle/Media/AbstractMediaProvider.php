<?php

namespace LineStorm\MediaBundle\Media;
use LineStorm\MediaBundle\Model\Media;
use LineStorm\MediaBundle\Model\MediaResizeProfile;
use LineStorm\SearchBundle\Search\SearchProviderInterface;

/**
 * Class AbstractMediaProvider
 * @package LineStorm\MediaBundle\Media
 */
abstract class AbstractMediaProvider
{
    /**
     * ID of the media provider
     *
     * @var string
     */
    protected $id;

    /**
     * The name of the form service
     *
     * @var string
     */
    protected $form;

    /**
     * @var SearchProviderInterface
     */
    protected $searchProvider;

    /**
     * This holds all the resaize names and sizes
     *
     * @var MediaResizeProfile[]
     */
    protected $mediaResizers;

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @inheritdoc
     */
    public function setSearchProvider(SearchProviderInterface $searchProvider)
    {
        $this->searchProvider = $searchProvider;
    }

    /**
     * Set the resize config
     *
     * @param MediaResizeProfile $resizeProfile
     */
    public function addMediaResizer(MediaResizeProfile $resizeProfile)
    {
        $this->mediaResizers[$resizeProfile->getName()] = $resizeProfile;
    }

    /**
     * Returns a resize mappings for a named profile
     *
     * @param $profile
     *
     * @return MediaResizeProfile
     */
    public function getResizeProfile($profile)
    {
        if(!array_key_exists($profile, $this->mediaResizers))
        {
            return false;
        }

        return $this->mediaResizers[$profile];
    }
} 
