<?php

namespace LineStorm\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LineStorm\MediaBundle\Model\MediaResizeProfile as BaseResizeProfile;

/**
 * Class ResizeProfile
 *
 * @package LineStorm\MediaBundle\Entity
 * @author  Andy Thorne <contrabandvr@gmail.com>
 */
abstract class MediaResizeProfile extends BaseResizeProfile
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

} 
