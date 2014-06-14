<?php

namespace LineStorm\MediaBundle\Tests\Fixtures\Entity;

use LineStorm\MediaBundle\Entity\MediaResizeProfile;

/**
 * Media entity fixture
 *
 * Class MediaEntity
 *
 * @package LineStorm\MediaBundle\Tests\Fixtures\Entity
 */
class MediaResizeProfileEntity extends MediaResizeProfile
{
    protected $id = 1;

    protected $name = 'test';

    protected $width = 200;

    protected $height = 200;
} 
