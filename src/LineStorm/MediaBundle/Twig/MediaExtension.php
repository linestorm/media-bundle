<?php

namespace LineStorm\MediaBundle\Twig;

/**
 * Twig extension functions
 *
 * Class MediaExtension
 *
 * @package LineStorm\CmsBundle\Twig
 */
class MediaExtension extends \Twig_Extension
{

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('get_image_size', array($this, 'getImageSize')),
        );
    }

    /**
     * @param $image
     *
     * @return array
     */
    public function getImageSize($image)
    {
        return @getimagesize($image);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'linestorm_cms_module_media_extension';
    }
}
