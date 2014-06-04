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
 * Class AdminController
 *
 * @package LineStorm\MediaBundle\Controller
 */
class CategoryController extends Controller
{

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

        $modelManager = $this->get('linestorm.cms.model_manager');
        $repo = $modelManager->get('media_category');

        $category = $repo->find($id);

        $form = $this->createForm('linestorm_cms_form_media_category', $category, array(
            'action' => $this->generateUrl('linestorm_cms_module_media_api_put_category', array('id' => $category->getId())),
            'method' => 'PUT',
        ));

        return $this->render('LineStormMediaBundle:Category:edit.html.twig', array(
            'category' => $category,
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

}
