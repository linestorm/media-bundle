<?php

namespace LineStorm\MediaBundle\Form\DataTransformer;

use LineStorm\MediaBundle\Media\MediaManager;
use LineStorm\MediaBundle\Model\Media;
use Symfony\Component\Form\DataTransformerInterface;

class MediaTransformer implements DataTransformerInterface
{
    /**
     * MediaManager
     *
     * @var MediaManager
     */
    private $mediaManager;

    /**
     * @param MediaManager $mediaManager
     */
    public function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    /**
     * Transforms the Document's value to a value for the form field
     */
    public function transform($data)
    {
        return $data;
    }

    /**
     * Transforms the value the users has typed to a value that suits the field in the Document
     */
    public function reverseTransform($data)
    {
        if($data instanceof Media)
        {
            if(!$data->getId() && $data->getHash())
            {
                $defaultProvider = $this->mediaManager->getDefaultProviderInstance();
                $fetched = $defaultProvider->findByHash($data->getHash());
                if($fetched instanceof Media)
                {
                    $fetched->setTitle($data->getTitle());
                    $fetched->setDescription($data->getDescription());
                    $fetched->setAlt($data->getAlt());
                    $fetched->setCredits($data->getCredits());
                    $fetched->setSeo($data->getSeo());

                    $data = $fetched;
                }
            }
        }

        return $data;
    }
}
