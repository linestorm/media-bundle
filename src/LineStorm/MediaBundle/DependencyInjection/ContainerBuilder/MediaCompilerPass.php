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

        foreach ($taggedServices as $id => $attributes) {
            $managerDefinition->addMethodCall(
                'addMediaProvider',
                array(new Reference($id))
            );
        }

        $managerDefinition->addMethodCall('setDefaultProvider', array($defaultProvider));
    }
} 
