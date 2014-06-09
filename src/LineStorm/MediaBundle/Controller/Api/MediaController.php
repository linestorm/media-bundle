<?php

namespace LineStorm\MediaBundle\Controller\Api;

use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use LineStorm\CmsBundle\Controller\Api\AbstractApiController;
use LineStorm\MediaBundle\Document\Media as MediaDocument;
use LineStorm\MediaBundle\Model\Media;
use LineStorm\MediaBundle\Model\MediaCategory;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * API for Media
 *
 * Class MediaController
 *
 * @package LineStorm\MediaBundle\Controller\Api
 * @author  Andy Thorne <contrabandvr@gmail.com>
 */
class MediaController extends AbstractApiController implements ClassResourceInterface
{

    /**
     * @return mixed
     * @throws AccessDeniedException
     *
     * [GET] /api/media/all.{_format}
     */
    public function getAllAction()
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');

        $provider = $this->getRequest()->query->get('p', null);
        $limit    = $this->getRequest()->query->get('limit', 50);
        $page     = $this->getRequest()->query->get('page', 50);

        $images = $mediaManager->findBy(array(
            'parent' => null,
        ), array(), $limit, $page, $provider);

        $json = array();
        foreach($images as $image)
            $json[] = $this->getMediaDocument($image);

        $view = View::create($json);

        return $this->get('fos_rest.view_handler')->handle($view);

    }

    /**
     * Fetch a media entity
     *
     * @param $id
     *
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * [GET] /api/media/{id}.{_format}
     */
    public function getAction($id)
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');

        $provider = $this->getRequest()->query->get('p', null);

        $image = $mediaManager->find($id, $provider);

        if(!($image instanceof Media))
        {
            throw $this->createNotFoundException("Media not found");
        }

        $view = View::create($this->getMediaDocument($image));

        return $this->get('fos_rest.view_handler')->handle($view);

    }


    /**
     * Resize a media object into all profiles
     *
     * @param $id
     *
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * [PATCH] /api/media/{id}/resize.{_format}
     */
    public function resizeAction($id)
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');

        $provider = $this->getRequest()->query->get('p', null);

        $image = $mediaManager->find($id, $provider);

        if(!($image instanceof Media))
        {
            throw $this->createNotFoundException("Media not found");
        }

        $resizedImages = $mediaManager->resize($image, $provider);

        $docs = array();
        foreach($resizedImages as $resizedImage)
        {
            $docs[] = $this->getMediaDocument($resizedImage);
        }

        $view = View::create($docs);

        return $this->get('fos_rest.view_handler')->handle($view);
    }

    /**
     * Search for a media entity
     *
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * [GET] /api/media/search/?q={query}
     */
    public function searchAction()
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');
        $provider     = $this->getRequest()->query->get('p', null);

        $query  = $this->getRequest()->query->get('q', null);
        $images = $mediaManager->search($query, $provider);

        $view = View::create($images);

        return $this->get('fos_rest.view_handler')->handle($view);
    }


    public function newAction()
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $form = $this->createForm('linestorm_cms_form_media_multiple', null, array(
            'action' => $this->generateUrl('linestorm_cms_module_media_api_post_media_batch'),
            'method' => 'POST',
        ));

        $view = $form->createView();


        $tpl  = $this->get('templating');
        $form = $tpl->render('LineStormMediaBundle:Form:form-multiple.html.twig', array(
            'form' => $view
        ));

        $rView = View::create(array(
            'form' => $form
        ));

        return $this->get('fos_rest.view_handler')->handle($rView);
    }

    /**
     * Create a new media entity
     *
     * @return Response
     * @throws AccessDeniedException
     *
     * [POST] /api/media.{_format}
     */
    public function postAction()
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');

        $request = $this->getRequest();
        $form    = $this->getForm();

        $formValues = json_decode($request->getContent(), true);

        $form->submit($formValues['linestorm_cms_form_media']);

        if($form->isValid())
        {
            /** @var Media $updatedMedia */
            $updatedMedia = $form->getData();
            $mediaManager->update($updatedMedia);
            $mediaManager->resize($updatedMedia);

            $view = $this->createResponse(array('location' => $this->generateUrl('linestorm_cms_module_media_api_get_media', array('id' => $form->getData()->getId()))), 200);
        }
        else
        {
            $view = View::create($form);
        }

        return $this->get('fos_rest.view_handler')->handle($view);

    }

    /**
     * Update a media entity
     *
     * @param $id
     *
     * @return Response
     * @throws AccessDeniedException
     *
     * [PUT] /api/media/{id}.{_format}
     */
    public function putAction($id)
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');

        $provider = $this->getRequest()->query->get('p', null);

        $image = $mediaManager->find($id, $provider);

        $request = $this->getRequest();
        $form    = $this->getForm($image);

        $formValues = json_decode($request->getContent(), true);

        $form->submit($formValues['linestorm_cms_form_media']);

        if($form->isValid())
        {
            /** @var Media $updatedMedia */
            $updatedMedia = $form->getData();
            $mediaManager->update($updatedMedia);
            $mediaManager->resize($updatedMedia);

            $view = $this->createResponse(array('location' => $this->generateUrl('linestorm_cms_module_media_api_get_media', array('id' => $form->getData()->getId()))), 200);
        }
        else
        {
            $view = View::create($form);
        }

        return $this->get('fos_rest.view_handler')->handle($view);
    }

    /**
     * @param $id
     *
     * @return Response
     * @throws AccessDeniedException
     *
     * [DELETE] /api/media/{id}.{_format}
     */
    public function deleteAction($id)
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');

        $provider = $this->getRequest()->query->get('p', null);

        $image = $mediaManager->find($id, $provider);

        $mediaManager->delete($image);

        $view = View::create(array(
            'message'  => 'The media has been deleted',
            'location' => $this->generateUrl('linestorm_cms_admin_module_media'),
        ));

        return $this->get('fos_rest.view_handler')->handle($view);
    }

    // BATCH METHODS

    /**
     * Create a batch of media entities
     *
     * @throws AccessDeniedException
     * @throws BadRequestHttpException
     * @return Response
     *
     * [POST] /blog/api/media/batches.{_format}
     */
    public function postBatchAction()
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');

        $request = $this->getRequest();
        $form    = $this->createForm('linestorm_cms_form_media_multiple');

        $payload = json_decode($request->getContent(), true);

        if(!array_key_exists('linestorm_cms_form_media_multiple', $payload))
        {
            throw new BadRequestHttpException("Expected form does not exist");
        }

        $form->submit($payload['linestorm_cms_form_media_multiple']);

        if($form->isValid())
        {
            $updatedMedia = $form->getData();
            $mediaDocs = array();

            /** @var Media $media */
            foreach($updatedMedia['media'] as $media)
            {
                $media->setUploader($this->getUser());
                $mediaManager->store($media);
                $mediaManager->resize($media);

                $mediaDocs[] = new MediaDocument($media);
            }

            $view = $this->createResponse($mediaDocs, 200);
        }
        else
        {
            $view = $this->createResponse($form);
        }


        return $this->get('fos_rest.view_handler')->handle($view);

    }

    /**
     * Batch put media
     *
     * @param $id
     *
     * @throws AccessDeniedException
     * @return Response
     */
    public function putBatchAction($id)
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');

        $provider     = $mediaManager->getDefaultProviderInstance();

        $form = $this->createForm('linestorm_cms_form_media_multiple', null, array(
            'action' => $this->generateUrl('linestorm_cms_module_media_api_post_media'),
            'method' => 'POST',
        ));

        return $this->render('LineStormMediaBundle:Form:multiple.html.twig', array(
            'form'  => $form->createView(),
        ));
    }


    /**
     * Get all media, given a tree and/or node list
     *
     * @param Request $request
     *
     * @return Response
     * @throws NotFoundHttpException
     *
     * [GET] /api/media/tree/expanded.{_format}
     */
    public function getTreeExpandedAction(Request $request)
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $nodes = $request->query->get('nodes', array());
        $categories = $request->query->get('categories', array());

        $modelManager = $this->getModelManager();
        $repo = $modelManager->get('media');
        $catRepo = $modelManager->get('media_category');

        $nodeList = array();
        if(is_array($nodes))
        {
            $qb = $repo->createQueryBuilder('m')
                ->where('m.id IN (:ids)')->setParameter('ids', $nodes);

            /** @var Media[] $nodes */
            $nodes = $qb->getQuery()->getResult();
            foreach($nodes as $node)
            {
                $nodeList[$node->getId()] = new MediaDocument($node);
            }
        }

        if(is_array($categories))
        {
            $hasChildren = true;
            while($hasChildren)
            {
                $qb = $catRepo->createQueryBuilder('c')
                    ->leftJoin('c.media', 'm')->addSelect('m')
                    ->leftJoin('c.children', 'ch')->addSelect('partial ch.{id}')
                    ->where('c.id IN (:ids)')->setParameter('ids', $categories);

                /** @var MediaCategory[] $catList */
                $catList = $qb->getQuery()->getResult();
                $categories = array(); // reset the id list
                foreach($catList as $cat)
                {
                    foreach($cat->getMedia() as $node)
                    {
                        $nodeList[$node->getId()] = new MediaDocument($node);
                    }

                    foreach($cat->getChildren() as $child)
                    {
                        $categories[] = $child->getId();
                    }
                }

                if(!count($categories))
                {
                    $hasChildren = false;
                }
            }

        }

        $view = View::create(array_values($nodeList));

        return $this->get('fos_rest.view_handler')->handle($view);
    }

    /**
     * Convert the media object to a document safe of the API
     *
     * @param Media $media
     *
     * @return MediaDocument
     */
    private function getMediaDocument(Media $media)
    {
        return new MediaDocument($media);
    }

    /**
     * Get the form for the entity
     *
     * @param Media|null $entity
     * @param array      $data
     *
     * @return Form
     */
    private function getForm($entity = null, array $data = array())
    {
        $mediaManager = $this->get('linestorm.cms.media_manager');
        $provider     = $mediaManager->getDefaultProviderInstance();

        return $this->createForm($provider->getForm(), $entity, $data);
    }

}
