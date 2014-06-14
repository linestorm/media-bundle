<?php

namespace LineStorm\MediaBundle\Media\Resizer;

use Doctrine\ORM\EntityManager;
use LineStorm\MediaBundle\Model\MediaResizeProfile;

/**
 * Class MediaResizeProfileManager
 *
 * @package LineStorm\MediaBundle\Media
 * @author  Andy Thorne <contrabandvr@gmail.com>
 */
class MediaResizeProfileManager
{
    /**
     * @var MediaResizeProfile[]
     */
    private $mediaResizeProfiles = array();

    /**
     * @var string
     */
    private $mediaResizeClass;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param $em
     * @param $mediaResizeClass
     */
    function __construct(EntityManager $em, $mediaResizeClass)
    {
        $this->em               = $em;
        $this->mediaResizeClass = $mediaResizeClass;
    }

    /**
     * Get a profile by name
     *
     * @param $name
     *
     * @return MediaResizeProfile
     */
    public function getProfile($name)
    {
        if(array_key_exists($name, $this->mediaResizeProfiles))
        {
            return $this->mediaResizeProfiles[$name];
        }

        $repo = $this->em->getRepository($this->mediaResizeClass);
        $profile = $repo->findOneBy(array( 'name' => $name ));

        if($profile instanceof MediaResizeProfile)
        {
            $this->mediaResizeProfiles[$profile->getName()] = $profile;

            return $profile;
        }

        return null;
    }

    /**
     * Get all profiles
     *
     * @return MediaResizeProfile[]
     */
    public function getProfiles()
    {
        $repo = $this->em->getRepository($this->mediaResizeClass);
        $profiles = $repo->findAll();

        foreach($profiles as $profile)
        {
            $this->mediaResizeProfiles[$profile->getName()] = $profile;
        }

        return $this->mediaResizeProfiles;
    }

}
