<?php

namespace LineStorm\MediaBundle\Form;

use LineStorm\MediaBundle\Form\DataTransformer\MediaTransformer;
use LineStorm\MediaBundle\Media\MediaManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaFormType extends AbstractType
{

    /**
     * MediaManager
     *
     * @var MediaManager
     */
    protected $mediaManager;

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
            ->add('title')
            ->add('description', 'textarea', array(
                'attr' => array(
                    'style' => 'height:200px;'
                ),
            ))
            ->add('credits')
            ->add('alt')
            ->add('seo')
            ->add('src', 'hidden')
            ->add('hash', 'hidden')
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
