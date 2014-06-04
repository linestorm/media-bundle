<?php

namespace LineStorm\MediaBundle\Media;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use LineStorm\MediaBundle\Media\Exception\MediaFileAlreadyExistsException;
use LineStorm\MediaBundle\Media\Exception\MediaFileDeniedException;
use LineStorm\MediaBundle\Model\Media;
use LineStorm\SearchBundle\Search\SearchProviderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Local storage media provider. This provider will store all images on the local disk.
 *
 * Class LocalStorageMediaProvider
 *
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
     * @var SearchProviderInterface
     */
    protected $searchProvider;

    /**
     * @param EntityManager           $em         The Entity Manager to use
     * @param string                  $mediaClass The Entity class
     * @param SecurityContext         $secutiryContext
     * @param string                  $path
     * @param string                  $src
     */
    function __construct(EntityManager $em, $mediaClass, SecurityContext $secutiryContext, $path, $src)
    {
        $this->em             = $em;
        $this->class          = $mediaClass;
        $this->storePath      = realpath($path) . DIRECTORY_SEPARATOR;
        $this->storeDirectory = $src;

        $token = $secutiryContext->getToken();
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
     * Return the categoryentity class FQNS
     *
     * @return string
     */
    public function getCategoryEntityClass()
    {
        return $this->class."Category";
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
            $newFileName = null;

            // if the media name is set, use it over a hashed one
            if($media->getName())
            {
                if($media->getPath() != $file->getPathname()) // if it's already in place, we don't need to move it!
                    $newFileName = $media->getName();
            }
            else
                $newFileName = md5(time() . rand()) . "." . $extension;

            if($newFileName)
                $file = $file->move($this->storePath, $newFileName);
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

            if(!$media->getNameOriginal())
                $media->setNameOriginal($oldName);

            if(!$media->getName())
                $media->setName($file->getFilename());

            if(!$media->getUploader())
                $media->setUploader($this->user);

            if(!$media->getAlt())
                $media->setAlt($oldPath['filename']);

            if(!$media->getCredits())
                $media->setCredits($this->user->getUsername());

            $media->setSrc($this->storeDirectory . $file->getFilename());
            $media->setPath($this->storePath . $file->getFilename());
            $media->setHash(sha1_file($file->getPathname()));

            $this->em->persist($media);
            $this->em->flush($media);

            // index the media object
            if($this->searchProvider instanceof SearchProviderInterface)
            {
                $this->searchProvider->index($media);
            }

            return $media;
        }
    }

    /**
     * @inheritdoc
     */
    public function update(Media $media)
    {

        // index the media object
        $this->searchProvider->index($media);

        $this->em->persist($media);
        $this->em->flush($media);

        return $media;
    }

    /**
     * @inheritdoc
     */
    public function delete(Media $media)
    {
        $file = $media->getPath();
        if($media->getSrc() && file_exists($file) && is_file($file))
            unlink($file);


        // index the media object
        $this->searchProvider->remove($media);

        $this->em->remove($media);
        $this->em->flush();
    }

    /**
     * Resize a media object to one or many profiles
     *
     * @param Media $media
     * @param array $profiles
     *
     * @return Media[]
     */
    public function resize(Media $media, array $profiles = array())
    {
        if(!count($profiles))
        {
            $profiles = array_keys($this->mediaResizers);
        }

        $resizedImages = array();
        foreach($profiles as $profile)
        {
            $resizer         = $this->getResizeProfile($profile);
            $resizedImages[] = $this->resizeImage($media, $resizer);
        }

        return $resizedImages;
    }


    /**
     * Resize a media object to a set size, returns the persisted media version object
     *
     * @param Media        $media
     * @param MediaResizer $resizer
     *
     * @return Media|null
     */
    protected function resizeImage(Media $media, MediaResizer $resizer)
    {
        $imagePath = pathinfo($media->getPath());

        $image = new ImageResize($media->getPath());

        $image->resizeTo($resizer->getX(), $resizer->getY());

        $newWidth  = $image->getResizeWidth();
        $newHeight = $image->getResizeHeight();

        $filename         = "{$imagePath['filename']}_{$newWidth}_x_{$newHeight}.{$imagePath['extension']}";
        $resizedImagePath = "{$imagePath['dirname']}" . DIRECTORY_SEPARATOR . "{$filename}";

        $image->saveImage($resizedImagePath);

        if(file_exists($resizedImagePath))
        {
            $file = new File($resizedImagePath);

            $class = $this->class;
            /** @var Media $resizedMedia */
            $resizedMedia = clone $media;
            $resizedMedia->setParent($media);
            $resizedMedia->setTitle("{$media->getTitle()} [{$resizer->getId()} {$newWidth} x {$newHeight}]");

            $resizedMedia->setName($filename);
            $resizedMedia->setNameOriginal($media->getName());

            $resizedMedia->setAlt($media->getAlt());
            $resizedMedia->setCredits($media->getCredits());
            $resizedMedia->setUploader($media->getUploader());

            try
            {
                return $this->store($file, $resizedMedia);
            } catch(MediaFileAlreadyExistsException $e)
            {
                return $e->getEntity();
            }
        }

    }

} 
