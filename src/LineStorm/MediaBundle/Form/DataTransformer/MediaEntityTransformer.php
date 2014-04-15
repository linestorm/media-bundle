<?php

namespace LineStorm\MediaBundle\Form\DataTransformer;

use LineStorm\MediaBundle\Media\MediaManager;
use LineStorm\MediaBundle\Model\Media;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class MediaEntityTransformer
 *
 * @package LineStorm\MediaBundle\Form\DataTransformer
 */
class MediaEntityTransformer implements DataTransformerInterface
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
     *
     * @param mixed $data
     *
     * @return int|null
     */
    public function transform($data)
    {
        if ($data instanceof Media)
        {
            return $data->getId();
        }

        return null;
    }

    /**
     * Transforms the value the users has typed to a value that suits the field in the Document
     *
     * @param mixed $data
     *
     * @return Media|null
     */
    public function reverseTransform($data)
    {
        if (!is_numeric($data))
        {
            return null;
        }

        $defaultProvider = $this->mediaManager->getDefaultProviderInstance();
        $fetched         = $defaultProvider->find($data);

        if ($fetched instanceof Media)
        {
            return $fetched;
        }

        return null;
    }
}
