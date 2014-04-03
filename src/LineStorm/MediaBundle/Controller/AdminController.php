<?php

namespace LineStorm\MediaBundle\Controller;

use FOS\RestBundle\View\View;
use LineStorm\MediaBundle\Media\Exception\MediaFileAlreadyExistsException;
use LineStorm\MediaBundle\Model\Media;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminController extends Controller
{

    public function listAction()
    {
        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin'))) {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.blog.media_manager');

        $providers = $mediaManager->getMediaProviders();

        return $this->render('LineStormMediaBundle:Admin:list.html.twig', array(
            'providers' => $providers,
        ));

    }


    public function editAction($id)
    {
        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin'))) {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.blog.media_manager');

        $media = $mediaManager->find($id);

        $form = $this->createForm('linestorm_blog_form_media', $media, array(
            'action' => $this->generateUrl('linestorm_blog_media_module_api_put_media', array('id' => $media->getId())),
            'method' => 'PUT',
        ));

        return $this->render('LineStormMediaBundle:Admin:edit.html.twig', array(
            'image'      => $media,
            'form'      => $form->createView(),
        ));
    }


    public function newAction()
    {
        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin'))) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm('linestorm_blog_form_media', null, array(
            'action' => $this->generateUrl('linestorm_blog_media_module_api_post_media'),
            'method' => 'POST',
        ));

        return $this->render('LineStormMediaBundle:Admin:new.html.twig', array(
            'image' => null,
            'form'  => $form->createView(),
        ));
    }


    public function uploadAction()
    {
        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin'))) {
            throw new AccessDeniedException();
        }


        $code = 201;
        try
        {
            $media = $this->doUpload();
        }
        catch(MediaFileAlreadyExistsException $e)
        {
            $media = $e->getEntity();
            $code = 200;
        }
        catch(HttpException $e)
        {
            throw $e;
        }
        catch(\Exception $e)
        {
            throw new HttpException(400, 'Upload Invalid', $e);
        }

        $view = View::create(new \LineStorm\MediaBundle\Document\Media($media), $code);
        $view->setFormat('json');
        return $this->get('fos_rest.view_handler')->handle($view);
    }

    public function uploadEditAction($id)
    {
        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin'))) {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.blog.media_manager');
        $image = $mediaManager->find($id);

        if(!($image instanceof Media))
        {
            throw $this->createNotFoundException("Image Not Found");
        }

        $code = 201;
        try
        {
            $image = $this->doUpload($image);
        }
        catch(MediaFileAlreadyExistsException $e)
        {
            $code = 200;
        }
        catch(HttpException $e)
        {
            throw $e;
        }
        catch(\Exception $e)
        {
            throw new HttpException(400, 'Upload Invalid', $e);
        }

        $view = View::create(new \LineStorm\MediaBundle\Document\Media($image), $code);
        $view->setFormat('json');
        return $this->get('fos_rest.view_handler')->handle($view);
    }


    private function doUpload($entity = null)
    {
        $mediaManager = $this->get('linestorm.blog.media_manager');

        $request = $this->getRequest();
        $files = $request->files->all();

        $totalFiles = count($files);

        // only allow single uploads
        if($totalFiles === 1) {
            /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
            $file = array_shift($files);
            $media = $mediaManager->store($file, $entity);
            if (!($media instanceof Media))
            {
                throw new HttpException(400, 'Upload Invalid');
            }
        }
        else
        {
            throw new HttpException(400, 'Upload Invalid: Too Many Files.');
        }

        return $media;
    }
}
