<?php

namespace LineStorm\MediaBundle\Media\Optimiser\Optimisers;

use LineStorm\MediaBundle\Media\Optimiser\AbstractOptimiseProfile;
use LineStorm\MediaBundle\Media\Optimiser\OptimiseProfileInterface;
use LineStorm\MediaBundle\Model\Media;

class ScriptOptimiser extends AbstractOptimiseProfile implements OptimiseProfileInterface
{

    /**
     * Optimise a media image
     *
     * @param Media $media
     *
     * @return mixed
     */
    public function optimise($media)
    {
        $info = getimagesize($media->getPath());

        switch($info['mime'])
        {
            case 'image/jpeg':
                exec("jpegoptim --strip-all \"{$media->getPath()}\"");
                break;
            case 'image/gif':
                $image = imagecreatefromgif($media->getPath());
                imagepng($image, $media->getPath());
                exec("optipng \"{$media->getPath()}\"");
                break;
            case 'image/png':
                exec("optipng \"{$media->getPath()}\"");
                break;
            default:
                return $media;
                break;
        }

        return $media;
    }

} 
