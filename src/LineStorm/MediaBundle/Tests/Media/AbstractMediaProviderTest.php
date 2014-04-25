<?php

namespace LineStorm\MediaBundle\Tests\Media;
use LineStorm\MediaBundle\Media\MediaProviderInterface;

/**
 * Unit tests for Media Provider
 *
 * Class LocalStorageMediaProviderTest
 *
 * @package LineStorm\MediaBundle\Tests\Media
 */
abstract class AbstractMediaProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $id;
    protected $form;

    /**
     * @param null $repository
     *
     * @return MediaProviderInterface
     */
    abstract protected function getProvider($repository = null);

    public function testId()
    {
        $provider = $this->getProvider();

        $id = $provider->getId();
        $this->assertEquals($id, $this->id);
    }

    public function testForm()
    {
        $provider = $this->getProvider();

        $form = $provider->getForm();
        $this->assertEquals($form, $this->form);
    }

    abstract public function testFind();
}
