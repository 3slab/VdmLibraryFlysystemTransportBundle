<?php

namespace Vdm\Bundle\LibraryFlysystemTransportBundle\Tests\Transport;

use League\Flysystem\FileAttributes;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\AbstractFlysystemExecutor;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\DefaultFlysystemExecutor;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Message\FlysystemMessage;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Tests\Fixtures\AppBundle\MessageHandler\FlysystemMessageHandler;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Tests\VdmLibraryFlysystemTransportKernelTestCase;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Transport\FlysystemTransport;

class FlysystemLocalTransportTest extends VdmLibraryFlysystemTransportKernelTestCase
{
    /**
     * {@inheritDoc}
     */
    protected static function getAppName(): string
    {
        return 'local';
    }

    public function testGet()
    {
        $getReturn = [1, 2, 3];
        $mockExecutor = $this->getMockBuilder(AbstractFlysystemExecutor::class)->getMock();
        $mockExecutor->expects($this->once())->method('get')->willReturn($getReturn);
        $mockExecutor->expects($this->never())->method('ack');
        $mockExecutor->expects($this->never())->method('reject');

        $transport = new FlysystemTransport($mockExecutor, new NullLogger());
        $this->assertEquals(
            $getReturn,
            $transport->get()
        );
    }

    public function testAck()
    {
        $envelope = new Envelope(new FlysystemMessage(""));

        $mockExecutor = $this->getMockBuilder(AbstractFlysystemExecutor::class)->getMock();
        $mockExecutor->expects($this->never())->method('get');
        $mockExecutor->expects($this->once())->method('ack')->with($envelope);
        $mockExecutor->expects($this->never())->method('reject');

        $transport = new FlysystemTransport($mockExecutor, new NullLogger());
        $transport->ack($envelope);
    }

    public function testReject()
    {
        $envelope = new Envelope(new FlysystemMessage(""));

        $mockExecutor = $this->getMockBuilder(AbstractFlysystemExecutor::class)->getMock();
        $mockExecutor->expects($this->never())->method('get');
        $mockExecutor->expects($this->never())->method('ack');
        $mockExecutor->expects($this->once())->method('reject')->with($envelope);

        $transport = new FlysystemTransport($mockExecutor, new NullLogger());
        $transport->reject($envelope);
    }

    public function testSend()
    {
        $transport = $this
            ->getMockBuilder(FlysystemTransport::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->expectException(\Exception::class);

        $envelope = new Envelope(new FlysystemMessage(""));
        $transport->send($envelope);
    }

    public function testFunctionalTransportGet()
    {
        $application = new Application(static::$kernel);
        $command = $application->find('messenger:consume');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'receivers' => ['source'],
        ]);

        /** @var FlysystemMessageHandler $handler */
        $handler = $this->getContainer()->get(FlysystemMessageHandler::class);

        $this->assertCount(2, $handler->files);

        /** @var FileAttributes $file1 */
        $file1 = $handler->files[0];
        $this->assertEquals('file', $file1->type());
        $this->assertEquals('01_file.txt', $file1->path());
        $this->assertEquals(15, $file1->fileSize());
        $this->assertEquals('public', $file1->visibility());
        $this->assertIsInt($file1->lastModified());
        $metadata = $file1->extraMetadata();
        $this->assertArrayHasKey(AbstractFlysystemExecutor::FILE_EXTRA_TMP_PATH, $metadata);
        $this->assertFileEquals(
            __DIR__ . '/../Fixtures/local/storage/source/01_file.txt',
            $metadata[AbstractFlysystemExecutor::FILE_EXTRA_TMP_PATH]
        );
        $this->assertArrayHasKey('executor', $metadata);
        $this->assertEquals('custom', $metadata['executor']);

        /** @var FileAttributes $file2 */
        $file2 = $handler->files[1];
        $this->assertEquals('file', $file2->type());
        $this->assertEquals('02_file.txt', $file2->path());
        $this->assertEquals(15, $file2->fileSize());
        $this->assertEquals('public', $file2->visibility());
        $this->assertIsInt($file2->lastModified());
        $metadata = $file2->extraMetadata();
        $this->assertArrayHasKey(AbstractFlysystemExecutor::FILE_EXTRA_TMP_PATH, $metadata);
        $this->assertFileEquals(
            __DIR__ . '/../Fixtures/local/storage/source/02_file.txt',
            $metadata[AbstractFlysystemExecutor::FILE_EXTRA_TMP_PATH]
        );
        $this->assertArrayHasKey('executor', $metadata);
        $this->assertEquals('custom', $metadata['executor']);
    }
}
