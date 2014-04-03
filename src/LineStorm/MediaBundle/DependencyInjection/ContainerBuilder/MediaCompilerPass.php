<?php

namespace LineStorm\MediaBundle\DependencyInjection\ContainerBuilder;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class MediaCompilerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('linestorm.blog.media_manager')) {
            return;
        }

        $definition = $container->getDefinition(
            'linestorm.blog.media_manager'
        );

        $defaultProvider = $container->getParameter('linestorm.blog.media_provider.default');

        $taggedServices = $container->findTaggedServiceIds(
            'linestorm.blog.media_provider'
        );

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addMediaProvider',
                array(new Reference($id))
            );
        }

        $definition->addMethodCall('setDefaultProvider', array($defaultProvider));
    }
} 
