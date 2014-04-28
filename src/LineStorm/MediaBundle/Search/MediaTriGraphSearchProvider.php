<?php

namespace LineStorm\MediaBundle\Search;

use LineStorm\MediaBundle\Model\Media;
use LineStorm\SearchBundle\Search\Provider\TriGraphSearchProvider;
use LineStorm\SearchBundle\Search\SearchProviderInterface;

/**
 * Class MediaTriGraphSearchProvider
 *
 * @package LineStorm\MediaBundle\Search
 */
class MediaTriGraphSearchProvider extends TriGraphSearchProvider implements SearchProviderInterface
{

    /**
     * @inheritdoc
     */
    public function getModel()
    {
        return 'media';
    }

    /**
     * @inheritdoc
     */
    public function getTriGraphModel()
    {
        return 'search_trigraph_media';
    }

    /**
     * @inheritdoc
     */
    public function getRoute($entity)
    {
        return '';
        if($entity instanceof Media)
        {
            return array(
                'linestorm_cms_post',
                array(
                    'category' => $entity->getCategory()->getName(),
                    'id'       => $entity->getId(),
                    'slug'     => $entity->getSlug(),
                )
            );
        }
        elseif(is_array($entity))
        {
            return array(
                'linestorm_cms_post',
                array(
                    'category' => $entity['category']['name'],
                    'id'       => $entity['id'],
                    'slug'     => $entity['slug'],
                )
            );
        }
    }


} 
