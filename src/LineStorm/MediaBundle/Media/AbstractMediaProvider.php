<?php

namespace LineStorm\MediaBundle\Media;

/**
 * Class AbstractMediaProvider
 * @package LineStorm\MediaBundle\Media
 */
abstract class AbstractMediaProvider
{
    /**
     * ID of the media provider
     *
     * @var string
     */
    protected $id;

    /**
     * The name of the form service
     *
     * @var string
     */
    protected $form;

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getForm()
    {
        return $this->form;
    }
} 
