<?php

/**
 * @package    3slab/VdmLibraryFlysystemTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryFlysystemTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryFlysystemTransportBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\AbstractFlysystemExecutor;

/**
 * Class VdmLibraryExtension
 *
 * @package Vdm\Bundle\LibraryFlysystemTransportBundle\DependencyInjection
 */
class VdmLibraryFlysystemTransportExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $container
            ->registerForAutoconfiguration(AbstractFlysystemExecutor::class)
            ->addTag('vdm_library.flysystem_executor')
        ;

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yaml');
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'vdm_library_flysystem_transport';
    }
}
