<?php

namespace LineStorm\MediaBundle\Media;
use LineStorm\MediaBundle\Model\Media;
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
     * @var MediaResizer[]
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
     * @param MediaResizer $resizer
     */
    public function addMediaResizer(MediaResizer $resizer)
    {
        $this->mediaResizers[$resizer->getId()] = $resizer;
    }

    /**
     * Returns a resize mappings for a named profile
     *
     * @param $profile
     *
     * @return MediaResizer
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
