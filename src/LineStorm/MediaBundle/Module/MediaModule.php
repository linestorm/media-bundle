<?php

namespace LineStorm\MediaBundle\Module\Media;

use LineStorm\MediaBundle\Media\MediaManager;
use LineStorm\BlogBundle\Module\AbstractModule;
use LineStorm\BlogBundle\Module\ModuleInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RouteCollection;

class MediaModule extends AbstractModule implements ModuleInterface
{
    protected $name = 'Media';
    protected $id = 'media';

    /**
     * @var MediaManager
     */
    protected $mediaManager;

    function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }


    /**
     * Returns the navigation array
     *
     * @return array
     */
    public function getNavigation()
    {
        return array(
            'View All' => array('linestorm_blog_admin_module_media', array()),
            'New Image' => array('linestorm_blog_admin_module_media_create', array()),
        );
    }

    /**
     * Thr route to load as 'home'
     *
     * @return string
     */
    public function getHome()
    {
        return 'linestorm_blog_admin_module_media';
    }

    /**
     * Add routes to the router
     * @param LoaderInterface $loader
     * @return RouteCollection
     */
    public function addRoutes(LoaderInterface $loader)
    {
        return $loader->import('@LineStormBlogBundle/Resources/config/routing/modules/media/media.yml', 'yaml');
    }
} 
