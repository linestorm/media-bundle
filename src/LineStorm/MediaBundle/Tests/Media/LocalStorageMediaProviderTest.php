<?php

namespace LineStorm\MediaBundle\Tests\Media;

use Doctrine\ORM\EntityManager;
use LineStorm\MediaBundle\Media\LocalStorageMediaProvider;
use LineStorm\MediaBundle\Media\MediaProviderInterface;
use LineStorm\MediaBundle\Media\Resizer\MediaResizer;
use LineStorm\MediaBundle\Media\Resizer\MediaResizeProfileManager;
use LineStorm\MediaBundle\Tests\Fixtures\Entity\MediaEntity;
use LineStorm\MediaBundle\Tests\Fixtures\Entity\MediaResizeProfileEntity;
use LineStorm\MediaBundle\Tests\Fixtures\Optimiser\MockOptimiser;
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

    /**
     * @var MediaResizeProfileManager
     */
    protected $resizeManager;

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
        $this->em = $this->getMock('\Doctrine\ORM\EntityManager', array('getRepository', 'persist', 'remove', 'flush', 'findAll'), array(), '', false);
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

        $this->resizeManager = new MediaResizeProfileManager($this->em, '');

        $provider = new LocalStorageMediaProvider($this->em, $entityClass, $sc, $this->resizeManager, $this->dir, '/');
        $provider->setOptimiser(new MockOptimiser());

        return $provider;
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
        $img = __DIR__.'/../Fixtures/Images/valid.gif';
        $tmpImg = $this->dir . '/valid.gif';
        copy($img, $tmpImg);

        $media = new MediaEntity();
        $media->setPath($tmpImg);

        $entityClass = '\LineStorm\MediaBundle\Tests\Fixtures\Entity\MediaEntity';
        $provider = $this->getProvider();
        $this->em->expects($this->once())
            ->method('persist')
            ->with($media)
            ->will($this->returnValue(null));
        $this->em->expects($this->once())
            ->method('flush')
            ->will($this->returnValue(null));


        $returnedMedia = $provider->store($media);

        $this->assertInstanceOf($entityClass, $returnedMedia);

        $uploader = $returnedMedia->getUploader();
        $this->assertInstanceOf('\LineStorm\MediaBundle\Tests\Fixtures\User\FakeAdminUser', $uploader);

    }


    public function testUpload()
    {
        $entityClass = '\LineStorm\MediaBundle\Tests\Fixtures\Entity\MediaEntity';
        $provider = $this->getProvider();

        $img = __DIR__.'/../Fixtures/Images/valid.gif';
        $tmpImg = $this->dir . '/valid.gif';
        copy($img, $tmpImg);

        $file = new File($tmpImg);

        $returnedMedia = $provider->upload($file);

        $this->assertInstanceOf($entityClass, $returnedMedia);

        $title = $returnedMedia->getTitle();
        $this->assertEquals($title, 'valid.gif');

        $hash = $returnedMedia->getHash();
        $this->assertEquals($hash, sha1_file($img));

        unlink($this->dir.$returnedMedia->getSrc());
    }

    public function testDelete()
    {
        $provider = $this->getProvider();
        $this->em->expects($this->once())
            ->method('remove')
            ->will($this->returnValue(null));

        $img = __DIR__.'/../Fixtures/Images/valid.gif';
        $tmpImg = $this->dir . '/valid.gif';
        copy($img, $tmpImg);


        $media = new MediaEntity();
        $media->setPath($tmpImg);
        $media->setSrc('/valid.gif');

        $provider->delete($media);

        // check the file was removed
        $this->assertFileNotExists($tmpImg);
    }

    /*public function testResize()
    {
        $entityClass = '\LineStorm\MediaBundle\Tests\Fixtures\Entity\MediaEntity';

        $repository = $this->getMock('\Doctrine\ORM\EntityRepository', array('findAll', 'findBy'), array(), '', false);
        $repository->expects($this->any())
                   ->method('findAll')
                   ->will($this->returnValue(array(new MediaResizeProfileEntity())));

        $provider = $this->getProvider($repository);

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

        /** @var MediaEntity $resizedEntity * /
        $resizedEntity = $resized[0];

        $dir = str_replace('/', '\/', realpath($this->dir));
        $this->assertRegExp('/'.$dir.'\/resize_valid_(\d+)_x_(\d+)\.gif$/', realpath($resizedEntity->getPath()));

    }*/
}
