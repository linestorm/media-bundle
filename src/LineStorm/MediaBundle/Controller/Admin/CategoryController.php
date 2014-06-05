<?php

namespace LineStorm\MediaBundle\Controller\Admin;

use LineStorm\MediaBundle\Model\MediaCategory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
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
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     * @return Response
     */
    public function editAction($id)
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            throw new AccessDeniedException();
        }

        $modelManager = $this->get('linestorm.cms.model_manager');
        $repo         = $modelManager->get('media_category');

        $category = $repo->find($id);

        if(!($category instanceof MediaCategory))
        {
            throw $this->createNotFoundException("Media Category Not Found");
        }

        $form = $this->createForm('linestorm_cms_form_media_category', $category, array(
            'action' => $this->generateUrl('linestorm_cms_module_media_api_put_category', array('id' => $category->getId())),
            'method' => 'PUT',
        ));

        return $this->render('LineStormMediaBundle:Category:edit.html.twig', array(
            'category' => $category,
            'form'     => $form->createView(),
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
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            throw new AccessDeniedException();
        }

        $modelManager = $this->get('linestorm.cms.model_manager');
        $class        = $modelManager->getEntityClass('media_category');

        $form = $this->createForm('linestorm_cms_form_media_category', new $class(), array(
            'action' => $this->generateUrl('linestorm_cms_module_media_api_post_category'),
            'method' => 'POST',
        ));

        return $this->render('LineStormMediaBundle:Category:new.html.twig', array(
            'image' => null,
            'form'  => $form->createView(),
        ));
    }

}
