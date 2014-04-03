<?php

namespace LineStorm\MediaBundle;

use LineStorm\BlogBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use LineStorm\MediaBundle\DependencyInjection\ContainerBuilder\MediaCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use LineStorm\BlogBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass as LocalDoctrineOrmMappingsPass;

class LineStormMediaBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MediaCompilerPass());

        $modelDir = realpath(__DIR__.'/Resources/config/model/doctrine');
        $mappings = array(
            $modelDir => 'LineStorm\MediaBundle\Model',
        );

        $ormCompilerClass = 'Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass';
        $localOrmCompilerClass = 'LineStorm\BlogBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass';
        if (class_exists($ormCompilerClass)) {
            $container->addCompilerPass(
                DoctrineOrmMappingsPass::createXmlMappingDriver(
                    $mappings,
                    array('linestorm_blog.entity_manager'),
                    'linestorm_blog.backend_type_orm'
                ));
        } elseif (class_exists($localOrmCompilerClass)) {
            $container->addCompilerPass(
                LocalDoctrineOrmMappingsPass::createXmlMappingDriver(
                    $mappings,
                    array('linestorm_blog.entity_manager'),
                    'linestorm_blog.backend_type_orm'
                ));
        } else {
            throw new \Exception("Unable to find ORM mapper");
        }

    }
}
