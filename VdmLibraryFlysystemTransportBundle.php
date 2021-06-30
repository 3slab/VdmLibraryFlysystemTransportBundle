<?php

/**
 * @package    3slab/VdmLibraryFlysystemTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryFlysystemTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryFlysystemTransportBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Vdm\Bundle\LibraryFlysystemTransportBundle\DependencyInjection\Compiler\FlysystemExecutorCompilerPass;

class VdmLibraryFlysystemTransportBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FlysystemExecutorCompilerPass());
    }
}
