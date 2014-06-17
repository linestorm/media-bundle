<?php

namespace LineStorm\MediaBundle\Form;

use LineStorm\MediaBundle\Form\DataTransformer\MediaTransformer;
use LineStorm\MediaBundle\Media\MediaManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class MediaFormType
 *
 * @package LineStorm\MediaBundle\Form
 */
class MediaMultipleFormType extends AbstractType
{

    /**
     * MediaManager
     *
     * @var MediaManager
     */
    protected $mediaManager;

    /**
     * @param MediaManager $mediaManager
     */
    function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('media', 'collection', array(
                'type' => new MediaFormType($this->mediaManager),
                'allow_add' => true,
                'allow_delete' => true,
            ))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'linestorm_cms_form_media_multiple';
    }
}
