<?php

namespace LineStorm\MediaBundle\Form\Type;

use LineStorm\MediaBundle\Form\DataTransformer\MediaEntityTransformer;
use LineStorm\MediaBundle\Media\MediaManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class MediaEntityType
 * @package LineStorm\MediaBundle\Form\Type
 */
class MediaEntityType extends AbstractType
{
    /**
     * @var MediaManager
     */
    protected  $mediaManager;

    /**
     * @param MediaManager $mediaManager
     */
    function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new MediaEntityTransformer($this->mediaManager);

        $builder->addModelTransformer($transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => $this->mediaManager->getDefaultProviderInstance()->getEntityClass(),
            'property' => 'title',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mediaentity';
    }
}
