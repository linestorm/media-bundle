<?php

namespace LineStorm\MediaBundle\Media;

use Doctrine\ORM\EntityManager;
use LineStorm\MediaBundle\Media\Exception\MediaFileAlreadyExistsException;
use LineStorm\MediaBundle\Media\Exception\MediaFileDeniedException;
use LineStorm\MediaBundle\Model\Media;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\SecurityContext;

class LocalStorageMediaProvider extends AbstractMediaProvider implements MediaProviderInterface
{
    protected $id = 'local_storeage';

    /**
     * @var EntityManager
     */
    protected $em;

    protected $class;

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
     * Where the files are stored!
     *
     * @var string
     */
    private $storeFolder = '/var/www/andythorne/web/media/';

    function __construct(EntityManager $em, $mediaClass, SecurityContext $secutiryContext)
    {
        $this->em = $em;
        $this->class = $mediaClass;
        $token = $secutiryContext->getToken();
        if($token)
            $this->user = $token->getUser();
    }


    public function find($id)
    {
        return $this->em->getRepository($this->class)->find($id);
    }

    public function findByHash($hash)
    {
        return $this->em->getRepository($this->class)->findOneBy(array( 'hash' => $hash));
    }

    public function findBy(array $criteria, array $order = array(), $limit = null, $offset = null)
    {
        $repo = $this->em->getRepository($this->class);

        return $repo->findBy($criteria, $order, $limit, $offset);
    }

    public function store(File $file, Media $media=null)
    {
        $hash = sha1_file($file->getPathname());
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
            $oldName = $file->getClientOriginalName();
        }
        else
        {
            $extension = $file->getExtension();
            $oldName = $file->getFilename();
        }

        $fileMime = $file->getMimeType();
        if (array_key_exists($fileMime, $this->accept) && in_array($extension, $this->accept[$fileMime])) {
            $newFileName = md5(time() . rand()) . "." . $extension;
            $file = $file->move($this->storeFolder, $newFileName);
        }
        else
        {
            throw new MediaFileDeniedException();
        }

        if($file instanceof File)
        {
            /** @var Media $media */
            if(!$media->getTitle())
                $media->setTitle($oldName);

            $media->setNameOriginal($oldName);
            $media->setName($file->getFilename());
            $media->setSrc('/media/'.$file->getFilename());
            $media->setHash(sha1_file($file->getPathname()));
            $media->setUploader($this->user);

            $this->em->persist($media);
            $this->em->flush($media);
            return $media;
        }
    }

    public function update(Media $media)
    {
        $this->em->persist($media);
        $this->em->flush($media);
    }

} 
