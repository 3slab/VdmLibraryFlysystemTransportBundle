<?php

namespace Vdm\Bundle\LibraryFlysystemTransportBundle\Tests\Transport;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\FlysystemBundle\Lazy\LazyFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionClass;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\DefaultFlysystemExecutor;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\FlysystemExecutorRegistry;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Transport\FlysystemTransportFactory;

class FlysystemLocalTransportFactoryTest extends TestCase
{
    public function testSupportsWrongDsn()
    {
        $mockFlysystem = $this->createMock(LazyFactory::class);
        $mockRegistry = $this->createMock(FlysystemExecutorRegistry::class);

        $factory = new FlysystemTransportFactory($mockFlysystem, $mockRegistry, new NullLogger());

        $this->assertFalse($factory->supports('vdm+doctrine://myconnection', []));
        $this->assertFalse($factory->supports('amqp://myconnection', []));
        $this->assertFalse($factory->supports('https://myconnection', []));
    }

    public function testSupportsNoStorage()
    {
        $this->expectException(\Exception::class);

        $mockFlysystem = $this->createMock(LazyFactory::class);
        $mockFlysystem
            ->expects($this->once())
            ->method('createStorage')
            ->with('mystorage', 'unusedvar')
            ->willThrowException(new \Exception('raised'));
        $mockRegistry = $this->createMock(FlysystemExecutorRegistry::class);

        $factory = new FlysystemTransportFactory($mockFlysystem, $mockRegistry, new NullLogger());
        $factory->supports('vdm+flysystem://mystorage', []);
    }

    public function testSupportsWithCustomStorage()
    {
        $mockFlysystem = $this->createMock(LazyFactory::class);
        $mockFlysystem
            ->expects($this->once())
            ->method('createStorage')
            ->with('mystorage', 'unusedvar')
            ->willReturn(new Filesystem(new LocalFilesystemAdapter(sys_get_temp_dir())));

        $mockRegistry = $this->createMock(FlysystemExecutorRegistry::class);

        $factory = new FlysystemTransportFactory($mockFlysystem, $mockRegistry, new NullLogger());
        $this->assertTrue($factory->supports('vdm+flysystem://mystorage', []));
    }

    public function testSupportsWithDefaultStorage()
    {
        $mockFlysystem = $this->createMock(LazyFactory::class);
        $mockFlysystem
            ->expects($this->once())
            ->method('createStorage')
            ->with('default.storage', 'unusedvar')
            ->willReturn(new Filesystem(new LocalFilesystemAdapter(sys_get_temp_dir())));

        $mockRegistry = $this->createMock(FlysystemExecutorRegistry::class);

        $factory = new FlysystemTransportFactory($mockFlysystem, $mockRegistry, new NullLogger());
        $this->assertTrue($factory->supports('vdm+flysystem://', []));
    }

    public function testCreateTransportDefaultExecutor()
    {
        $serializer = $this->createMock(SerializerInterface::class);

        $storage = new Filesystem(new LocalFilesystemAdapter(sys_get_temp_dir()));
        $mockFlysystem = $this->createMock(LazyFactory::class);
        $mockFlysystem
            ->expects($this->once())
            ->method('createStorage')
            ->with('default.storage', 'unusedvar')
            ->willReturn($storage);

        $executor = new DefaultFlysystemExecutor();
        $mockRegistry = $this->createMock(FlysystemExecutorRegistry::class);
        $mockRegistry
            ->expects($this->once())
            ->method('getDefault')
            ->willReturn($executor);
        $mockRegistry
            ->expects($this->never())
            ->method('get');

        $options = ['key' => 'value'];
        $factory = new FlysystemTransportFactory($mockFlysystem, $mockRegistry, new NullLogger());
        $transport = $factory->createTransport('vdm+flysystem://', $options, $serializer);

        $this->assertEquals($storage, $executor->getStorage());
        $this->assertEquals($options, $executor->getOptions());
        $this->assertEquals($executor, $this->extractProtectedExecutor($transport));
    }

    public function testCreateTransportCustomExecutor()
    {
        $serializer = $this->createMock(SerializerInterface::class);

        $storage = new Filesystem(new LocalFilesystemAdapter(sys_get_temp_dir()));
        $mockFlysystem = $this->createMock(LazyFactory::class);
        $mockFlysystem
            ->expects($this->once())
            ->method('createStorage')
            ->with('default.storage', 'unusedvar')
            ->willReturn($storage);

        $executor = new DefaultFlysystemExecutor();
        $mockRegistry = $this->createMock(FlysystemExecutorRegistry::class);
        $mockRegistry
            ->expects($this->once())
            ->method('get')
            ->with('My\\Executor\\Class')
            ->willReturn($executor);
        $mockRegistry
            ->expects($this->never())
            ->method('getDefault');

        $options = ['key' => 'value', 'flysystem_executor' => 'My\\Executor\\Class'];
        $factory = new FlysystemTransportFactory($mockFlysystem, $mockRegistry, new NullLogger());
        $transport = $factory->createTransport('vdm+flysystem://', $options, $serializer);

        $this->assertEquals($storage, $executor->getStorage());
        $this->assertEquals($options, $executor->getOptions());
        $this->assertEquals($executor, $this->extractProtectedExecutor($transport));
    }

    protected function extractProtectedExecutor($transport)
    {
        $reflection = new ReflectionClass($transport);
        $property = $reflection->getProperty('executor');
        $property->setAccessible(true);
        return $property->getValue($transport);
    }
}
