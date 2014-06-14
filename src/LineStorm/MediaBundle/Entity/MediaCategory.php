<?php

namespace LineStorm\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LineStorm\MediaBundle\Model\MediaCategory as BaseMediaCategory;

/**
 * Class MediaCategory
 *
 * @package LineStorm\MediaBundle\Entity
 * @author  Andy Thorne <contrabandvr@gmail.com>
 */
abstract class MediaCategory extends BaseMediaCategory
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
     * @var MediaCategory[]
     *
     * @ORM\OneToMany(targetEntity="MediaCategory", mappedBy="parent")
     */
    protected $children;

    /**
     * @var MediaCategory
     *
     * @ORM\ManyToOne(targetEntity="MediaCategory", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @var Media
     *
     * @ORM\OneToMany(targetEntity="Media", mappedBy="category", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $media;

} 
