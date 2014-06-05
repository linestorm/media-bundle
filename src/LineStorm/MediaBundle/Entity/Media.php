<?php

namespace LineStorm\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LineStorm\MediaBundle\Model\Media as BaseMedia;

/**
 * Base Media Entity
 *
 * Class Media
 *
 * @package LineStorm\MediaBundle\Entity
 */
abstract class Media extends BaseMedia
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var Media
     *
     * @ORM\OneToMany(targetEntity="Media", mappedBy="parent")
     */
    protected $children;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="Media", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @var MediaCategory
     *
     * @ORM\ManyToOne(targetEntity="MediaCategory", inversedBy="media", cascade={"persist"})
     */
    protected $category;

} 
