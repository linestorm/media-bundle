<?php

namespace LineStorm\MediaBundle\DependencyInjection\ContainerBuilder;

use LineStorm\MediaBundle\Media\MediaResizer;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

class MediaCompilerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('linestorm.cms.media_manager')) {
            return;
        }

        $managerDefinition = $container->getDefinition(
            'linestorm.cms.media_manager'
        );

        $defaultProvider = $container->getParameter('linestorm.cms.media_provider.default');

        $taggedServices = $container->findTaggedServiceIds(
            'linestorm.cms.media_provider'
        );

        $resizeConfig = $container->getParameter('linestorm.cms.media.image_resize_config');

        $resizers = array();
        foreach($resizeConfig as $profile => $config)
        {
            @list($x,$y) = $config;

            $serviceId = 'linestorm.cms.media.image_resizer.'.$profile;
            $definition = new Definition('LineStorm\MediaBundle\Media\MediaResizer', array(
                $profile,
                new Reference('doctrine.orm.entity_manager'),
                $x,
                $y
            ));

            $container->setDefinition($serviceId, $definition);
            $resizers[] = $serviceId;
        }

        foreach ($taggedServices as $id => $attributes) {
            $managerDefinition->addMethodCall(
                'addMediaProvider',
                array(new Reference($id))
            );

            foreach($resizers as $rid)
            {
                $container->getDefinition($id)->addMethodCall(
                    'addMediaResizer',
                    array(new Reference($rid))
                );
            }
        }

        $managerDefinition->addMethodCall('setDefaultProvider', array($defaultProvider));
    }
} 
