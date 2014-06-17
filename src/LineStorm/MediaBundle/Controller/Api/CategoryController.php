<?php

namespace LineStorm\MediaBundle\Controller\Api;

use Doctrine\ORM\Query;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use LineStorm\CmsBundle\Api\ApiExceptionResponse;
use LineStorm\CmsBundle\Controller\Api\AbstractApiController;
use LineStorm\MediaBundle\Model\MediaCategory;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Query\Expr;

/**
 * Media Category Contriller
 *
 * Class CategoryController
 *
 * @package LineStorm\MediaBundle\Controller\Api
 */
class CategoryController extends AbstractApiController implements ClassResourceInterface
{
    /**
     * easy access to the form type name
     *
     * @var string
     */
    private $formName = 'linestorm_cms_form_media_category';

    /**
     * Create a new Category Form
     *
     * @return Response
     * @throws AccessDeniedException
     *
     * [GET] /api/media/categories/new.{_format}
     */
    public function newAction()
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $form = $this->createForm($this->formName, null, array(
            'action' => $this->generateUrl('linestorm_cms_module_media_api_post_category'),
            'method' => 'POST',
        ));

        $view = $form->createView();

        /** @var \Symfony\Bundle\FrameworkBundle\Templating\Helper\FormHelper $tpl */
        $tpl  = $this->get('templating.helper.form');
        $form = $tpl->form($view);

        $rView = View::create(array(
            'form' => $form
        ));

        return $this->get('fos_rest.view_handler')->handle($rView);
    }

    /**
     * Get all categories
     *
     * @return Response
     *
     * [GET] /api/media/categories.{_format}
     */
    public function cgetAction()
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $modelManager = $this->getModelManager();

        $categories = $modelManager->get('media_category')->findAll();

        $view = View::create($categories);

        return $this->get('fos_rest.view_handler')->handle($view);

    }

    /**
     * Get a single Category
     *
     * @param $id
     *
     * @return Response
     * @throws NotFoundHttpException
     *
     * [GET] /api/media/categories/{id}.{_format}
     */
    public function getAction($id)
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $modelManager = $this->getModelManager();

        $category = $modelManager->get('media_category')->find($id);
        if(!($category instanceof MediaCategory))
        {
            throw $this->createNotFoundException("Category not found");
        }

        $view = View::create($category);

        return $this->get('fos_rest.view_handler')->handle($view);

    }

    /**
     * Get a single Category
     *
     * @param $id
     *
     * @return Response
     * @throws NotFoundHttpException
     *
     * [GET] /api/media/categories/{id}/children.{_format}
     */
    public function getChildrenAction($id)
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $modelManager = $this->getModelManager();
        $entity = $modelManager->get('media_category');

        $qb = $entity->createQueryBuilder('m')
            ->join('m.parent', 'p')
            ->where('p.id = :parentId')->setParameter('parentId', $id);

        $categories = $qb->getQuery()->getArrayResult();

        foreach($categories as &$category)
        {
            $category['url'] = $this->generateUrl('linestorm_cms_module_media_api_get_category_children', array('id' => $category['id']));
        }

        $view = View::create($categories);

        return $this->get('fos_rest.view_handler')->handle($view);
    }

    /**
     * Get a tree root
     *
     * @param Request $request
     *
     * @return Response
     * @throws NotFoundHttpException
     *
     * [GET] /api/media/category/tree.{_format}
     */
    public function getTreeAction(Request $request)
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $modelManager = $this->getModelManager();
        $entity = $modelManager->get('media_category');

        $node = $request->query->get('id', null);
        $to = $request->query->get('to', null);
        $excludeNodes = $request->query->get('exclude', array());

        if(is_numeric($node))
        {
            $qb = $entity->createQueryBuilder('m')
                ->leftJoin('m.children', 'c')->addSelect('c')
                ->where('m.id = ?1')->setParameter(1, $node);
        }
        elseif(is_numeric($to))
        {
            // get the target entity
            $node = $entity->find($to);

            // traverse up till we reach the root node
            $found = false;
            $limit = 10;
            $i = 1;
            $tree = array();
            $rootNode = null;
            $parentNode = $node;
            while(!$found && $i <= $limit)
            {
                $cNode = $parentNode->getParent();
                if($cNode === null)
                {
                    $found = true;
                    $rootNode = $parentNode;
                }
                else
                {
                    $parentNode = $cNode;
                }

                $tree[] = $parentNode->getId();
                ++$i;
            }

            $qb = $entity->createQueryBuilder('m0');
            $rTree = array_reverse($tree);

            for($i=0 ; $i<count($rTree) ; ++$i)
            {
                $id = $rTree[$i];
                $n = $i+1;
                $qb->leftJoin("m{$i}.children", "m{$n}", Expr\Join::WITH, "m{$n}.id NOT IN (:ids)")->setParameter('ids', $excludeNodes)->addSelect("m{$n}");
            }

            $qb->where('m0.parent IS NULL');

        }
        else
        {
            $qb = $entity->createQueryBuilder('m0')
                ->leftJoin('m0.children', 'c')->addSelect('c')
                ->where('m0.parent IS NULL')
                ->andwhere('m0.id NOT IN (:ids)')->setParameter('ids', $excludeNodes);
        }

        $categories = $qb->getQuery()->getArrayResult();

        $view = View::create($categories);

        return $this->get('fos_rest.view_handler')->handle($view);
    }


    /**
     * Get a tree root
     *
     * @param Request $request
     *
     * @return Response
     * @throws NotFoundHttpException
     *
     * [GET] /api/media/category/mediatree.{_format}
     */
    public function getMediatreeAction(Request $request)
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $modelManager = $this->getModelManager();
        $repo = $modelManager->get('media_category');

        $node = $request->query->get('id', null);

        $qb = $repo->createQueryBuilder('c')
           ->leftJoin('c.media', 'm')
           ->leftJoin('c.children', 'ch')
           ->addSelect('m,ch')
           ->andWhere('m.parent IS NULL');

        if(is_numeric($node))
        {
            $qb->andWhere('c.id = ?1')->setParameter(1, $node);
        }
        else
        {
            $qb->andWhere('c.parent IS NULL');
        }

        $categories = $qb->getQuery()->getArrayResult();

        foreach($categories as &$category)
        {
            $category['url'] = $this->generateUrl('linestorm_cms_admin_module_media_category_edit', array('id' => $category['id']));

            foreach($category['media'] as &$media)
            {
                $media['url'] = $this->generateUrl('linestorm_cms_admin_module_media_edit', array('id' => $media['id']));
            }

            foreach($category['children'] as &$child)
            {
                $child['url'] = $this->generateUrl('linestorm_cms_admin_module_media_category_edit', array('id' => $child['id']));
            }
        }

        $view = View::create($categories);

        return $this->get('fos_rest.view_handler')->handle($view);
    }

    /**
     * Save a Category
     *
     * @throws AccessDeniedException
     * @throws BadRequestHttpException
     * @return Response
     *
     * [POST] /api/media/categories.{_format}
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
        $provider = $mediaManager->getDefaultProviderInstance();
        $modelManager = $this->getModelManager();

        // linestorm_cms_form_media_category
        $request = $this->getRequest();
        $form    = $this->createForm($this->formName);

        $payload = json_decode($request->getContent(), true);

        $formValues = $payload[$this->formName];

        // clean up the indexes
        if(array_key_exists('media', $formValues))
        {
            $formValues['media'] = array_values($formValues['media']);
        }
        $form->submit($formValues);

        if($form->isValid())
        {
            $em  = $modelManager->getManager();

            /** @var MediaCategory $updatedCategory */
            $updatedCategory = $form->getData();

            // force update the category
            $medias = $updatedCategory->getMedia();
            foreach($medias as $media)
            {
                $media->setCategory($updatedCategory);
                $provider->store($media);
                $provider->resize($media);
            }

            if($updatedCategory === $updatedCategory->getParent())
            {
                throw new BadRequestHttpException("You cannot make a category a parent of itself");
            }
            $em->persist($updatedCategory);
            $em->flush();

            $view = $this->createResponse(array('location' => $this->generateUrl('linestorm_cms_module_media_api_get_category', array('id' => $updatedCategory->getId()))), 200);
        }
        else
        {
            $view = View::create($form);
        }

        return $this->get('fos_rest.view_handler')->handle($view);
    }

    /**
     * Update a Category
     *
     * @param $id
     *
     * @throws AccessDeniedException
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @return Response
     *
     * [PUT] /api/media/categories/{id}.{_format}
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
        $provider = $mediaManager->getDefaultProviderInstance();
        $modelManager = $this->getModelManager();

        $category = $modelManager->get('media_category')->find($id);
        if(!($category instanceof MediaCategory))
        {
            throw $this->createNotFoundException("Category not found");
        }

        // linestorm_cms_form_media_category
        $request = $this->getRequest();
        $form    = $this->getForm($category);

        $payload = json_decode($request->getContent(), true);

        $formValues = $payload[$this->formName];

        $form->submit($formValues);

        if($form->isValid())
        {
            $em  = $modelManager->getManager();

            /** @var MediaCategory $updatedCategory */
            $updatedCategory = $form->getData();

            if($category === $updatedCategory->getParent())
            {
                throw new BadRequestHttpException("You cannot make a category a parent of itself");
            }

            // force update the category
            $medias = $updatedCategory->getMedia();
            foreach($medias as $media)
            {
                $media->setUploader($this->getUser());
                if(!$media->getId())
                {
                    $provider->store($media);
                    $provider->resize($media);
                }
                else
                {
                    $provider->update($media);
                }
            }

            $em->persist($updatedCategory);
            $em->flush();

            $view = $this->createResponse(array('location' => $this->generateUrl('linestorm_cms_module_media_api_get_category', array('id' => $updatedCategory->getId()))), 200);
        }
        else
        {
            $view = View::create($form);
        }

        return $this->get('fos_rest.view_handler')->handle($view);
    }

    /**
     * Delete a category
     *
     * @param $id
     *
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * [DELETE] /api/media/categories/{id}.{_format}
     */
    public function deleteAction($id)
    {
        $user = $this->getUser();
        if(!($user instanceof UserInterface) || !($user->hasGroup('admin')))
        {
            $view = View::create(new ApiExceptionResponse('Access Denied', 403));
            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $modelManager = $this->getModelManager();

        $category = $modelManager->get('media_category')->find($id);
        if(!($category instanceof MediaCategory))
        {
            throw $this->createNotFoundException("Category not found");
        }

        $em = $modelManager->getManager();
        $em->remove($category);
        $em->flush();

        $view = View::create(array(
            'location' => $this->generateUrl('linestorm_cms_admin_module_media_view'),
        ));

        return $this->get('fos_rest.view_handler')->handle($view);
    }

    /**
     * Get an instance of the conroller form
     *
     * @param null $entity
     *
     * @return Form
     */
    private function getForm($entity = null)
    {
        return $this->createForm($this->formName, $entity);
    }
}
