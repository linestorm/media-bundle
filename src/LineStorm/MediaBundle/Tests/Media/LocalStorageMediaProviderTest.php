<?php

namespace LineStorm\MediaBundle\Tests\Media;

use Doctrine\ORM\EntityManager;
use LineStorm\MediaBundle\Media\LocalStorageMediaProvider;
use LineStorm\MediaBundle\Media\MediaProviderInterface;
use LineStorm\MediaBundle\Media\MediaResizer;
use LineStorm\MediaBundle\Tests\Fixtures\Entity\MediaEntity;
use LineStorm\MediaBundle\Tests\Fixtures\User\FakeAdminUser;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Unit tests for Local Storeage Media Provider
 *
 * Class LocalStorageMediaProviderTest
 *
 * @package LineStorm\MediaBundle\Tests\Media
 */
class LocalStorageMediaProviderTest extends AbstractMediaProviderTest
{
    protected $id = 'local_storeage';
    protected $form = 'linestorm_cms_form_media';
    protected $dir;

    /**
     * @var FakeAdminUser
     */
    protected $user;

    /**
     * @var EntityManager
     */
    protected $em;

    protected function setUp()
    {
        $this->dir = __DIR__."/../Fixtures/tmp";
        $this->user = new FakeAdminUser();

        if(!file_exists($this->dir))
            @mkdir($this->dir, 0777, true);

        parent::setUp();
    }

    protected function tearDown()
    {
        if(file_exists($this->dir))
        {
            foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
            }
            rmdir($this->dir);
        }
    }


    /**
     * @param null $repository
     *
     * @return MediaProviderInterface
     */
    protected function getProvider($repository = null)
    {
        $entityClass = '\LineStorm\MediaBundle\Tests\Fixtures\Entity\MediaEntity';
        $this->em = $this->getMock('\Doctrine\ORM\EntityManager', array('getRepository', 'persist', 'flush'), array(), '', false);
        $sc = $this->getMock('\Symfony\Component\Security\Core\SecurityContext', array('getToken'), array(), '', false);

        $token = new UsernamePasswordToken($this->user, 'unittest', 'unittest');
        $sc->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token));

        if($repository)
        {
            $this->em->expects($this->once())
                ->method('getRepository')
                ->will($this->returnValue($repository));
        }

        return new LocalStorageMediaProvider($this->em, $entityClass, $sc, $this->dir, '/');
    }


    public function testFind()
    {
        $entityClass = '\LineStorm\MediaBundle\Tests\Fixtures\Entity\MediaEntity';

        $repository = $this->getMock('\Doctrine\ORM\EntityRepository', array('find'), array(), '', false);
        $repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->will($this->returnValue(new $entityClass()));

        $provider = $this->getProvider($repository);

        $returnedEntity = $provider->find(1);

        $this->assertInstanceOf($entityClass, $returnedEntity);
    }

    public function testFindBy()
    {
        $entityClass = '\LineStorm\MediaBundle\Tests\Fixtures\Entity\MediaEntity';

        $repository = $this->getMock('\Doctrine\ORM\EntityRepository', array('findBy'), array(), '', false);
        $repository->expects($this->once())
            ->method('findBy')
            ->with(array('id' => 1), array(), 1, 0)
            ->will($this->returnValue(array(new $entityClass())));

        $provider = $this->getProvider($repository);

        $returnedEntities = $provider->findBy(array('id' => 1), array(), 1, 0);

        $this->assertTrue(is_array($returnedEntities));
        $this->assertArrayHasKey(0, $returnedEntities);
        $this->assertInstanceOf($entityClass, $returnedEntities[0]);
    }

    public function testStore()
    {
        $entityClass = '\LineStorm\MediaBundle\Tests\Fixtures\Entity\MediaEntity';

        $repository = $this->getMock('\Doctrine\ORM\EntityRepository', array('findOneBy'), array(), '', false);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        $provider = $this->getProvider($repository);

        $img = __DIR__.'/../Fixtures/Images/valid.gif';
        copy($img, '/tmp/valid.gif');

        $file = new File('/tmp/valid.gif');

        $returnedMedia = $provider->store($file);

        $this->assertInstanceOf($entityClass, $returnedMedia);

        $title = $returnedMedia->getTitle();
        $this->assertEquals($title, 'valid.gif');

        $hash = $returnedMedia->getHash();
        $this->assertEquals($hash, sha1_file($img));

        $uploader = $returnedMedia->getUploader();
        $this->assertInstanceOf('\LineStorm\MediaBundle\Tests\Fixtures\User\FakeAdminUser', $uploader);

        unlink($this->dir.$returnedMedia->getSrc());
    }

    public function testDelete()
    {
        $entityClass = '\LineStorm\MediaBundle\Tests\Fixtures\Entity\MediaEntity';

        $repository = $this->getMock('\Doctrine\ORM\EntityRepository', array('findOneBy'), array(), '', false);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        $provider = $this->getProvider($repository);

        $img = __DIR__.'/../Fixtures/Images/valid.gif';
        copy($img, '/tmp/valid.gif');

        $file = new File('/tmp/valid.gif');

        $returnedMedia = $provider->store($file);

        $this->assertInstanceOf($entityClass, $returnedMedia);

        $title = $returnedMedia->getTitle();
        $this->assertEquals($title, 'valid.gif');

        $hash = $returnedMedia->getHash();
        $this->assertEquals($hash, sha1_file($img));

        $uploader = $returnedMedia->getUploader();
        $this->assertInstanceOf('\LineStorm\MediaBundle\Tests\Fixtures\User\FakeAdminUser', $uploader);

        unlink($this->dir.$returnedMedia->getSrc());
    }

    public function testResize()
    {
        $entityClass = '\LineStorm\MediaBundle\Tests\Fixtures\Entity\MediaEntity';

        $repository = $this->getMock('\Doctrine\ORM\EntityRepository', array('findOneBy'), array(), '', false);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        $provider = $this->getProvider($repository);

        $resizer = new MediaResizer('20x20', $this->em, 20, 20);
        $provider->addMediaResizer($resizer);

        $entity = new MediaEntity();

        $img = __DIR__.'/../Fixtures/Images/valid.gif';
        $tmpImg = $this->dir.'/resize_valid.gif';
        copy($img, $tmpImg);

        $entity->setPath($tmpImg);
        $entity->setUploader($this->user);

        $resized = $provider->resize($entity);

        $this->assertTrue(is_array($resized));
        $this->assertCount(1, $resized);
        $this->assertArrayHasKey(0, $resized);

        /** @var MediaEntity $resizedEntity */
        $resizedEntity = $resized[0];

        $dir = str_replace('/', '\/', realpath($this->dir));
        $this->assertRegExp('/'.$dir.'\/resize_valid_(\d+)_x_(\d+)\.gif$/', realpath($resizedEntity->getPath()));

    }
}
