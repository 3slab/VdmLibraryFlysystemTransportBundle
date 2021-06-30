<?php

namespace Vdm\Bundle\LibraryFlysystemTransportBundle\Tests\Executor;

use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Vdm\Bundle\LibraryBundle\Stamp\StopAfterHandleStamp;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\AbstractFlysystemExecutor;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\DefaultFlysystemExecutor;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Message\FlysystemMessage;

class DefaultFlysystemExecutorTest extends TestCase
{
    public function testGet()
    {
        $storage = new Filesystem(
            new LocalFilesystemAdapter(__DIR__ . '/../Fixtures/local/storage/source')
        );

        $executor = new DefaultFlysystemExecutor();
        $executor->setStorage($storage);

        $envelopes = iterator_to_array($executor->get());

        dump($envelopes);

        $this->assertCount(2, $envelopes);

        usort($envelopes, function ($a, $b) {
            $fileA = $a->getMessage()->getPayload();
            $fileB = $b->getMessage()->getPayload();
            return ($fileA->path() < $fileB->path()) ? -1 : 1;
        });

        dump($envelopes);

        $this->assertInstanceOf(Envelope::class, $envelopes[0]);
        $this->assertNull($envelopes[0]->last(StopAfterHandleStamp::class));
        $message1 = $envelopes[0]->getMessage();
        $this->assertInstanceOf(FlysystemMessage::class, $message1);
        /** @var FileAttributes $file1 */
        $file1 = $message1->getPayload();
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

        $this->assertInstanceOf(Envelope::class, $envelopes[1]);
        $stopStamp = $envelopes[1]->last(StopAfterHandleStamp::class);
        $this->assertInstanceOf(StopAfterHandleStamp::class, $stopStamp);
        $message2 = $envelopes[1]->getMessage();
        $this->assertInstanceOf(FlysystemMessage::class, $message2);
        /** @var FileAttributes $file2 */
        $file2 = $message2->getPayload();
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
    }
}
