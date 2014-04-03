<?php

namespace LineStorm\MediaBundle\Controller\Api;

use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use LineStorm\BlogBundle\Controller\Api\AbstractApiController;
use LineStorm\MediaBundle\Model\Media;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use LineStorm\MediaBundle\Document\Media as MediaDocument;

class MediaController extends AbstractApiController implements ClassResourceInterface
{

    private function getMediaDocument(Media $media)
    {
        return new MediaDocument($media);
    }


    /**
     * @return mixed
     * @throws AccessDeniedException
     *
     * [GET] /blog/admin/modules/media/api/media.{_format}
     */
    public function getAllAction()
    {
        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin'))) {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.blog.media_manager');

        $provider = $this->getRequest()->query->get('p', null);
        $limit = $this->getRequest()->query->get('limit', 50);
        $page = $this->getRequest()->query->get('page', 50);

        $images = $mediaManager->findBy(array(), array(), $limit, $page, $provider);

        $json = array();
        foreach($images as $image)
            $json[] = $this->getMediaDocument($image);

        $view = View::create($json);
        return $this->get('fos_rest.view_handler')->handle($view);

    }

    public function getAction($id)
    {
        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin'))) {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.blog.media_manager');

        $provider = $this->getRequest()->query->get('p', null);

        $image = $mediaManager->find($id, $provider);

        if(!($image instanceof Media))
        {
            throw $this->createNotFoundException("Media not found");
        }

        $view = View::create($this->getMediaDocument($image));
        return $this->get('fos_rest.view_handler')->handle($view);

    }

    public function postAction()
    {
        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin'))) {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.blog.media_manager');

        $request = $this->getRequest();
        $form = $this->getForm();

        $formValues = json_decode($request->getContent(), true);

        $form->submit($formValues['linestorm_blog_form_media']);

        if ($form->isValid())
        {
            /** @var Media $updatedMedia */
            $updatedMedia = $form->getData();
            $mediaManager->update($updatedMedia);

            $view = $this->createResponse(array('location' => $this->generateUrl('linestorm_blog_media_module_api_get_media', array( 'id' => $form->getData()->getId()))), 200);
        }
        else
        {
            $view = View::create($form);
        }

        return $this->get('fos_rest.view_handler')->handle($view);

    }

    public function putAction($id)
    {
        $user = $this->getUser();
        if (!($user instanceof UserInterface) || !($user->hasGroup('admin'))) {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('linestorm.blog.media_manager');

        $provider = $this->getRequest()->query->get('p', null);

        $image = $mediaManager->find($id, $provider);

        $request = $this->getRequest();
        $form = $this->getForm($image);

        $formValues = json_decode($request->getContent(), true);

        $form->submit($formValues['linestorm_blog_form_media']);

        if ($form->isValid())
        {
            /** @var Media $updatedMedia */
            $updatedMedia = $form->getData();
            $mediaManager->update($updatedMedia);

            $view = $this->createResponse(array('location' => $this->generateUrl('linestorm_blog_media_module_api_get_media', array( 'id' => $form->getData()->getId()))), 200);
        }
        else
        {
            $view = View::create($form);
        }

        return $this->get('fos_rest.view_handler')->handle($view);
    }


    private function getForm($entity = null)
    {
        return $this->createForm('linestorm_blog_form_media', $entity);
    }

}
