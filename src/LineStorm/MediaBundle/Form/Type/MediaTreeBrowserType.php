<?php

namespace LineStorm\MediaBundle\Form\Type;

use LineStorm\CmsBundle\Form\AbstractCmsFormType;
use LineStorm\CmsBundle\Form\DataTransformer\EntityTransformer;
use LineStorm\CmsBundle\Model\ModelManager;
use LineStorm\CmsBundle\Module\ModuleManager;
use LineStorm\MediaBundle\Form\DataTransformer\MediaEntityTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class MediaTreeBrowserType
 * @package LineStorm\MediaBundle\Form\Type
 */
class MediaTreeBrowserType extends AbstractCmsFormType
{

    /**
     * @var MediaEntityTransformer
     */
    protected $transformer;

    /**
     * @param ModelManager  $modelManager
     * @param ModuleManager $moduleManager
     */
    function __construct(ModelManager $modelManager, ModuleManager $moduleManager = null)
    {
        parent::__construct($modelManager, $moduleManager);

        $this->transformer = new EntityTransformer($modelManager->getManager(), $modelManager->getEntityClass('media_category'));
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }


    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $node = $this->transformer->reverseTransform($view->vars['value']);
        $view->vars['node'] = $node;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => $this->modelManager->getEntityClass('media_category'),
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
        return 'mediatreebrowser';
    }
}
