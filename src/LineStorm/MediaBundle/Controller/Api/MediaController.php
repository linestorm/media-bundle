<?php

namespace LineStorm\MediaBundle\Controller\Api;

use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use LineStorm\CmsBundle\Controller\Api\AbstractApiController;
use LineStorm\MediaBundle\Document\Media as MediaDocument;
use LineStorm\MediaBundle\Model\Media;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * API for media bundle
 *
 * Class MediaController
 *
 * @package LineStorm\MediaBundle\Controller\Api
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
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');

        $provider = $this->getRequest()->query->get('p', null);
        $limit    = $this->getRequest()->query->get('limit', 50);
        $page     = $this->getRequest()->query->get('page', 50);

        $images = $mediaManager->findBy(array(), array(), $limit, $page, $provider);

        $json = array();
        foreach ($images as $image)
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
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');

        $provider = $this->getRequest()->query->get('p', null);

        $image = $mediaManager->find($id, $provider);

        if (!($image instanceof Media))
        {
            throw $this->createNotFoundException("Media not found");
        }

        $view = View::create($this->getMediaDocument($image));

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
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');
        $provider = $this->getRequest()->query->get('p', null);

        $query = $this->getRequest()->query->get('q', null);
        $images = $mediaManager->search($query, $provider);

        $view = View::create($images);
        return $this->get('fos_rest.view_handler')->handle($view);
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
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');

        $request = $this->getRequest();
        $form    = $this->getForm();

        $formValues = json_decode($request->getContent(), true);

        $form->submit($formValues['linestorm_cms_form_media']);

        if ($form->isValid())
        {
            /** @var Media $updatedMedia */
            $updatedMedia = $form->getData();
            $mediaManager->update($updatedMedia);

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
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.cms.media_manager');

        $provider = $this->getRequest()->query->get('p', null);

        $image = $mediaManager->find($id, $provider);

        $request = $this->getRequest();
        $form    = $this->getForm($image);

        $formValues = json_decode($request->getContent(), true);

        $form->submit($formValues['linestorm_cms_form_media']);

        if ($form->isValid())
        {
            /** @var Media $updatedMedia */
            $updatedMedia = $form->getData();
            $mediaManager->update($updatedMedia);

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
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     *
     * [DELETE] /api/media/{id}.{_format}
     */
    public function deleteAction($id)
    {

        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            throw new AccessDeniedException();
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
     *
     * @return Form
     */
    private function getForm($entity = null)
    {
        $mediaManager = $this->get('linestorm.cms.media_manager');
        $provider     = $mediaManager->getDefaultProviderInstance();

        return $this->createForm($provider->getForm(), $entity);
    }

}
