<?php

namespace LineStorm\MediaBundle\Tests\Module;

use LineStorm\CmsBundle\Module\ModuleInterface;
use LineStorm\CmsBundle\Tests\Module\ModuleTest;
use LineStorm\MediaBundle\Module\MediaModule;

/**
 * Unit tests for media module
 *
 * Class MediaModuleTest
 *
 * @package LineStorm\MediaBundle\Tests\Module
 */
class MediaModuleTest extends ModuleTest
{
    protected $id = 'media';
    protected $name = 'Media';

    /**
     * @return ModuleInterface
     */
    protected function getModule()
    {
        return new MediaModule();
    }
} 
