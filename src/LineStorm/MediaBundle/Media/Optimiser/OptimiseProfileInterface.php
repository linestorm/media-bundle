<?php

namespace LineStorm\MediaBundle\Media\Optimiser;

use LineStorm\MediaBundle\Model\Media;

interface OptimiseProfileInterface
{
    /**
     * Optimise a media image
     *
     * @param Media $media
     *
     * @return mixed
     */
    public function optimise($media);
} 
