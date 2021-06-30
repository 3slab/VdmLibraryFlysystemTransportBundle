<?php

namespace Vdm\Bundle\LibraryFlysystemTransportBundle\Tests\Executor;

use PHPUnit\Framework\TestCase;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\DefaultFlysystemExecutor;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\FlysystemExecutorRegistry;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Tests\Fixtures\AppBundle\Executor\CustomFlysystemExecutor;

class FlysystemExecutorRegistryTest extends TestCase
{
    public function testGetDefaultNotAvailable()
    {
        $this->expectException(\RuntimeException::class);

        $registry = new FlysystemExecutorRegistry();
        $registry->addExecutor(new CustomFlysystemExecutor(), 'custom');

        $registry->getDefault();
    }

    public function testGetNotAvailable()
    {
        $this->expectException(\RuntimeException::class);

        $registry = new FlysystemExecutorRegistry();
        $registry->addExecutor(new CustomFlysystemExecutor(), 'custom');

        $registry->get('unknown');
    }

    public function testGetDefault()
    {
        $defaultExecutor = new DefaultFlysystemExecutor();

        $registry = new FlysystemExecutorRegistry();
        $registry->addExecutor($defaultExecutor, 'default');
        $registry->addExecutor(new CustomFlysystemExecutor(), 'custom');

        $this->assertEquals($defaultExecutor, $registry->getDefault());
    }

    public function testGet()
    {
        $defaultExecutor = new DefaultFlysystemExecutor();
        $customExecutor = new CustomFlysystemExecutor();

        $registry = new FlysystemExecutorRegistry();
        $registry->addExecutor($defaultExecutor, 'default');
        $registry->addExecutor($customExecutor, 'custom');

        $this->assertEquals($customExecutor, $registry->get('custom'));
        $this->assertEquals($defaultExecutor, $registry->get('default'));
    }
}
