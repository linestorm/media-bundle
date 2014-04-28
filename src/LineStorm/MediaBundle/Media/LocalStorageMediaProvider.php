<?php

namespace LineStorm\MediaBundle\Media;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use LineStorm\MediaBundle\Media\Exception\MediaFileAlreadyExistsException;
use LineStorm\MediaBundle\Media\Exception\MediaFileDeniedException;
use LineStorm\MediaBundle\Model\Media;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Local storage media provider. This provider will store all images on the local disk.
 *
 * Class LocalStorageMediaProvider
 * @package LineStorm\MediaBundle\Media
 */
class LocalStorageMediaProvider extends AbstractMediaProvider implements MediaProviderInterface
{
    /**
     * @var string
     */
    protected $id = 'local_storeage';

    /**
     * @var string
     */
    protected $form = 'linestorm_cms_form_media';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var UserInterface|null
     */
    protected $user;

    /**
     * Mime types that are accepted
     *
     * @var array
     */
    private $accept = array(
        'image/jpeg' => array('jpg', 'jpeg'),
        'image/png'  => array('png'),
        'image/gif'  => array('gif'),
    );

    /**
     * Where the files are stored in the web folder
     *
     * @var string
     */
    private $storeDirectory;

    /**
     * This is the local path to the web folder
     *
     * @var string
     */
    private $storePath;

    /**
     * @param EntityManager $em The Entity Manager to use
     * @param string $mediaClass The Entity class
     * @param SecurityContext $secutiryContext
     * @param $webRoot
     * @param string $dir
     */
    function __construct(EntityManager $em, $mediaClass, SecurityContext $secutiryContext, $webRoot, $dir)
    {
        $this->em    = $em;
        $this->class = $mediaClass;
        $this->storePath = $webRoot;
        $this->storeDirectory = $dir;

        $token       = $secutiryContext->getToken();
        if($token)
        {
            $this->user = $token->getUser();
        }
    }

    /**
     * Return the entity class FQNS
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->class;
    }

    /**
     * @inheritdoc
     */
    public function find($id)
    {
        return $this->em->getRepository($this->class)->find($id);
    }

    /**
     * @inheritdoc
     */
    public function findByHash($hash)
    {
        return $this->em->getRepository($this->class)->findOneBy(array('hash' => $hash));
    }

    /**
     * @inheritdoc
     */
    public function findBy(array $criteria, array $order = array(), $limit = null, $offset = null)
    {
        $repo = $this->em->getRepository($this->class);

        return $repo->findBy($criteria, $order, $limit, $offset);
    }

    /**
     * @inheritdoc
     */
    public function search($query)
    {
        return $this->searchProvider->search($query, Query::HYDRATE_ARRAY);
    }

    /**
     * @inheritdoc
     */
    public function store(File $file, Media $media = null)
    {
        $hash   = sha1_file($file->getPathname());
        $entity = $this->findByHash($hash);
        if($entity instanceof Media)
        {
            throw new MediaFileAlreadyExistsException($entity);
        }

        if(!($media instanceof Media))
        {
            $media = new $this->class();
        }

        if($file instanceof UploadedFile)
        {
            $extension = $file->getClientOriginalExtension();
            $oldName   = $file->getClientOriginalName();
        }
        else
        {
            $extension = $file->getExtension();
            $oldName   = $file->getFilename();
        }

        $fileMime = $file->getMimeType();
        if(array_key_exists($fileMime, $this->accept) && in_array(strtolower($extension), $this->accept[$fileMime]))
        {
            $newFileName = md5(time() . rand()) . "." . $extension;
            $file        = $file->move($this->storePath.$this->storeDirectory, $newFileName);
        }
        else
        {
            throw new MediaFileDeniedException($fileMime);
        }

        if($file instanceof File)
        {
            /** @var Media $media */
            if(!$media->getTitle())
            {
                $media->setTitle($oldName);
            }

            $oldPath = pathinfo($oldName);

            $media->setNameOriginal($oldName);
            $media->setName($file->getFilename());
            $media->setDescription('Uploaded by '.$this->user->getUsername());
            $media->setSrc($this->storeDirectory.$file->getFilename());
            $media->setHash(sha1_file($file->getPathname()));
            $media->setUploader($this->user);
            $media->setAlt($oldPath['filename']);
            $media->setCredits($this->user->getUsername());

            $this->em->persist($media);
            $this->em->flush($media);

            return $media;
        }
    }

    /**
     * @inheritdoc
     */
    public function update(Media $media)
    {
        $this->em->persist($media);
        $this->em->flush($media);

        return $media;
    }

    /**
     * @inheritdoc
     */
    public function delete(Media $media)
    {
        $file = $this->storePath.$media->getSrc();
        if($media->getSrc() && file_exists($file) && is_file($file))
            unlink($file);
        $this->em->remove($media);
        $this->em->flush();
    }


} 
