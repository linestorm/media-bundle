<?php

namespace LineStorm\MediaBundle\Media;

use LineStorm\MediaBundle\Model\Media;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class MediaManager
 *
 * @package LineStorm\MediaBundle\Media
 */
class MediaManager implements \Countable
{
    /**
     * @var MediaProviderInterface[]
     */
    private $mediaProviders = array();

    private $defaultProvider = null;

    /**
     * Get the total number of media providers
     *
     * @return int
     */
    public function count()
    {
        return count($this->mediaProviders);
    }

    /**
     * Get all the media providers
     *
     * @return MediaProviderInterface[]
     */
    public function getMediaProviders()
    {
        return $this->mediaProviders;
    }

    /**
     * Add a media provider to the stack
     *
     * @param MediaProviderInterface $mediaProvider
     */
    public function addMediaProvider(MediaProviderInterface $mediaProvider)
    {
        $this->mediaProviders[$mediaProvider->getId()] = $mediaProvider;
    }

    /**
     * Set the default media provider by Id
     *
     * @param string $provider
     */
    public function setDefaultProvider($provider)
    {
        $this->defaultProvider = $provider;
    }

    /**
     * @return MediaProviderInterface
     */
    public function getDefaultProviderInstance()
    {
        return $this->mediaProviders[$this->defaultProvider];
    }

    /**
     * Search the media providers for
     *
     * @param string $id Image identifier
     * @param string $provider Provider Identifier
     *
     * @return null
     */
    public function find($id, $provider=null)
    {
        if($provider && array_key_exists($provider, $this->mediaProviders))
        {
            return $this->mediaProviders[$provider]->find($id);
        }
        else
        {
            $provider = $this->getDefaultProviderInstance();
            return $provider->find($id);
        }
    }

    /**
     * Search the media providers for
     *
     * @param array $terms
     * @param string $provider Provider Identifier
     *
     * @internal param string $id Image identifier
     * @return null
     */
    public function findBy(array $terms, $provider=null)
    {
        if($provider && array_key_exists($provider, $this->mediaProviders))
        {
            return $this->mediaProviders[$provider]->findBy($terms);
        }
        else
        {
            $provider = $this->getDefaultProviderInstance();
            return $provider->findBy($terms);
        }
    }

    /**
     * Store the file into the bank
     *
     * @param File   $file
     * @param Media  $media
     * @param string $provider
     *
     * @return Media
     */
    public function store(File $file, Media $media=null, $provider=null)
    {
        if($provider && array_key_exists($provider, $this->mediaProviders))
        {
            return $this->mediaProviders[$provider]->store($file, $media);
        }
        else
        {
            return $this->mediaProviders[$this->defaultProvider]->store($file, $media);
        }
    }

    /**
     * Update a media model
     *
     * @param Media $media
     * @param null  $provider
     *
     * @return Media
     */
    public function update(Media $media, $provider=null)
    {
        if($provider && array_key_exists($provider, $this->mediaProviders))
        {
            return $this->mediaProviders[$provider]->update($media);
        }
        else
        {
            return $this->mediaProviders[$this->defaultProvider]->update($media);
        }
    }


    /**
     * Delete a media model
     *
     * @param Media $media
     * @param null  $provider
     *
     * @return Media
     */
    public function delete(Media $media, $provider=null)
    {
        if($provider && array_key_exists($provider, $this->mediaProviders))
        {
            $this->mediaProviders[$provider]->delete($media);
        }
        else
        {
            $this->mediaProviders[$this->defaultProvider]->delete($media);
        }
    }
}
