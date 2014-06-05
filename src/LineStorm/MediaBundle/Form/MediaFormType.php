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
class MediaFormType extends AbstractType
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
        $defaultProvider = $this->mediaManager->getDefaultProviderInstance();
        $builder
            ->add('title', 'textarea')
            ->add('category', 'mediatreebrowser', array(
                'class'    => $defaultProvider->getCategoryEntityClass(),
                'property' => 'name',
            ))
            ->add('credits')
            ->add('alt')
            ->add('src', 'hidden')
            ->add('hash', 'hidden')
            ->add('name', 'hidden')
            ->add('nameOriginal', 'hidden')
            ->add('path', 'hidden')
        ;

        $transformer = new MediaTransformer($this->mediaManager);

        $builder->addModelTransformer($transformer);
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $defaultProvider = $this->mediaManager->getDefaultProviderInstance();
        $resolver->setDefaults(array(
            'label' => false,
            'data_class' => $defaultProvider->getEntityClass()
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'linestorm_cms_form_media';
    }
}
