<?php

namespace LineStorm\MediaBundle\Module;

use LineStorm\CmsBundle\Module\AbstractModule;
use LineStorm\CmsBundle\Module\ModuleInterface;
use Symfony\Component\Config\Loader\Loader;

/**
 * Class MediaModule
 * @package LineStorm\MediaBundle\Module
 */
class MediaModule extends AbstractModule implements ModuleInterface
{
    protected $name = 'Media';
    protected $id = 'media';

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
