<?php

namespace LineStorm\MediaBundle\Controller\Admin;

use FOS\RestBundle\View\View;
use LineStorm\MediaBundle\Media\Exception\MediaFileAlreadyExistsException;
use LineStorm\MediaBundle\Model\Media;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class MediaController
 *
 * @package LineStorm\MediaBundle\Controller
 */
class MediaController extends Controller
{

    /**
     * List all media items
     *
     * @return Response
     * @throws AccessDeniedException
     */
    public function listAction()
    {
        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');

        $providers = $mediaManager->getMediaProviders();

        return $this->render('LineStormMediaBundle:Admin:list.html.twig', array(
            'providers' => $providers,
        ));

    }

    /**
     * Edit a media entity
     *
     * @param $id
     *
     * @return Response
     * @throws AccessDeniedException
     */
    public function editAction($id)
    {
        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');
        $provider     = $mediaManager->getDefaultProviderInstance();

        $media = $mediaManager->find($id);

        $form = $this->createForm($provider->getForm(), $media, array(
            'action' => $this->generateUrl('linestorm_cms_module_media_api_put_media', array('id' => $media->getId())),
            'method' => 'PUT',
        ));

        return $this->render('LineStormMediaBundle:Admin:edit.html.twig', array(
            'image' => $media,
            'form'  => $form->createView(),
        ));
    }

    /**
     * Create a media entity
     *
     * @return Response
     * @throws AccessDeniedException
     */
    public function newAction()
    {
        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');
        $provider     = $mediaManager->getDefaultProviderInstance();
        $class = $provider->getEntityClass();

        $form = $this->createForm($provider->getForm(), new $class(), array(
            'action' => $this->generateUrl('linestorm_cms_module_media_api_post_media'),
            'method' => 'POST',
        ));

        return $this->render('LineStormMediaBundle:Admin:new.html.twig', array(
            'image' => null,
            'form'  => $form->createView(),
        ));
    }

    /**
     * Upload a media entity
     *
     * @return Response
     * @throws AccessDeniedException
     */
    public function uploadAction()
    {
        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            throw new AccessDeniedException();
        }


        $code = 201;
        try
        {
            $media = $this->doUpload();
        } catch (MediaFileAlreadyExistsException $e)
        {
            $media = $e->getEntity();
            $code  = 200;
        } catch (\Exception $e)
        {
            $view = View::create(array(
                'error' => $e->getMessage(),
            ), 400);
            $view->setFormat('json');

            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $api = array(
            'edit' => $this->generateUrl('linestorm_cms_module_media_api_put_media', array('id' => $media->getId())),
        );
        $doc = new \LineStorm\MediaBundle\Document\Media($media, $api);
        $view = View::create($doc, $code);
        $view->setFormat('json');

        return $this->get('fos_rest.view_handler')->handle($view);
    }

    /**
     * Upload an item for a entity we are editing
     *
     * @param $id
     *
     * @return Response
     * @throws AccessDeniedException
     * @throws HttpException
     * @throws NotFoundHttpException
     */
    public function uploadEditAction($id)
    {
        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');
        $image        = $mediaManager->find($id);

        if (!($image instanceof Media))
        {
            throw $this->createNotFoundException("Image Not Found");
        }

        $code = 201;
        try
        {
            $image = $this->doUpload($image);
        } catch (MediaFileAlreadyExistsException $e)
        {
            $code = 200;
        } catch (HttpException $e)
        {
            $view = View::create($e);
            $view->setFormat('json');

            return $this->get('fos_rest.view_handler')->handle($view);
        } catch (\Exception $e)
        {
            throw new HttpException(400, 'Upload Invalid', $e);
        }

        $view = View::create(new \LineStorm\MediaBundle\Document\Media($image), $code);
        $view->setFormat('json');

        return $this->get('fos_rest.view_handler')->handle($view);
    }


    /**
     * Handle a file upload
     *
     * @param Media|null $entity The entity to store into
     *
     * @return Media
     * @throws HttpException
     */
    private function doUpload($entity = null)
    {
        $mediaManager = $this->get('linestorm.cms.media_manager');

        $request = $this->getRequest();
        $files   = $request->files->all();

        $totalFiles = count($files);

        // only allow single uploads
        if ($totalFiles === 1)
        {
            /* @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
            $file = array_shift($files);

            if ($file->isValid())
            {
                $media = $mediaManager->store($file, $entity);

                if (!($media instanceof Media))
                {
                    throw new HttpException(400, 'Upload Invalid');
                }

                return $media;
            }
            else
            {
                switch ($file->getError())
                {
                    case UPLOAD_ERR_INI_SIZE:
                        throw new HttpException(400, 'Upload Invalid: File too large for server');
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        throw new HttpException(400, 'Upload Invalid: File too large for form');
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        throw new HttpException(400, 'Upload Invalid: Only a partial file was recieved');
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        throw new HttpException(400, 'Upload Invalid: No file given');
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        throw new HttpException(400, 'Upload Invalid: Unable to store (1)');
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        throw new HttpException(400, 'Upload Invalid: Unable to store (2)');
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        throw new HttpException(400, 'Upload Invalid: Invalid Extension');
                        break;
                }
            }
        }
        else
        {
            throw new HttpException(400, 'Upload Invalid: Too Many Files.');
        }
    }
}
