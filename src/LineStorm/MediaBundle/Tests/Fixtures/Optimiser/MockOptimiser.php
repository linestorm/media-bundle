<?php

namespace LineStorm\MediaBundle\Tests\Fixtures\Optimiser;

use LineStorm\MediaBundle\Media\Optimiser\OptimiseProfileInterface;
use LineStorm\MediaBundle\Model\Media;

class MockOptimiser implements OptimiseProfileInterface
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
        return $media;
        // TODO: Implement optimise() method.
    }

} 
