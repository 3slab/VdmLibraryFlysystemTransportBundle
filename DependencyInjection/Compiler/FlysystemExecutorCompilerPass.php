<?php

/**
 * @package    3slab/VdmLibraryFlysystemTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryFlysystemTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryFlysystemTransportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\FlysystemExecutorRegistry;

/**
 * Class FlysystemExecutorCompilerPass
 * @package Vdm\Bundle\LibraryFlysystemTransportBundle\DependencyInjection\Compiler
 */
class FlysystemExecutorCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(FlysystemExecutorRegistry::class)) {
            return;
        }

        $definition = $container->getDefinition(FlysystemExecutorRegistry::class);
        $taggedServices = $container->findTaggedServiceIds('vdm_library.flysystem_executor');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addExecutor', [new Reference($id), $id]);
        }
    }
}
