<?php

namespace LineStorm\MediaBundle\Module;

use LineStorm\MediaBundle\Media\MediaManager;
use LineStorm\CmsBundle\Module\AbstractModule;
use LineStorm\CmsBundle\Module\ModuleInterface;
use Symfony\Component\Config\Loader\Loader;
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
            'View All' => array('linestorm_cms_admin_module_media', array()),
            'New Image' => array('linestorm_cms_admin_module_media_create', array()),
        );
    }

    /**
     * Thr route to load as 'home'
     *
     * @return string
     */
    public function getHome()
    {
        return 'linestorm_cms_admin_module_media';
    }

    /**
     * @inheritdoc
     */
    public function addRoutes(Loader $loader)
    {
        return $loader->import('@LineStormMediaBundle/Resources/config/routing/api.yml', 'rest');
    }

    /**
     * @inheritdoc
     */
    public function addAdminRoutes(Loader $loader)
    {
        return $loader->import('@LineStormMediaBundle/Resources/config/routing/admin.yml', 'yaml');
    }
} 
