<?php

namespace LineStorm\MediaBundle\Media;

use LineStorm\MediaBundle\Model\Media;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Interface that all media providers must implement
 *
 * Interface MediaProviderInterface
 * @package LineStorm\MediaBundle\Media
 */
interface MediaProviderInterface
{

    /**
     * Get the storage provider's id
     *
     * @return string
     */
    public function getId();

    /**
     * Get a edit/new Form for the media
     *
     * @return string
     */
    public function getForm();

    /**
     * Get an image from storage
     *
     * @param $id
     *
     * @return Media
     */
    public function find($id);

    /**
     * @param array   $criteria
     * @param array   $order
     * @param integer $limit
     * @param integer $offset
     *
     * @return Media|null
     */
    public function findBy(array $criteria, array $order = array(), $limit = null, $offset = null);

    /**
     * Find an image by the hash. This can stop duplicate images being uploaded.
     *
     * @param string $hash
     *
     * @return Media|null
     */
    public function findByHash($hash);

    /**
     * Store an image in the storeage
     *
     * @param File  $file
     * @param Media $media
     *
     * @return mixed
     */
    public function store(File $file, Media $media = null);

    /**
     * Update an image in storage
     *
     * @param Media $media
     *
     * @return mixed
     */
    public function update(Media $media);
} 
