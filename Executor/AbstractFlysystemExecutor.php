<?php

/**
 * @package    3slab/VdmLibraryFlysystemTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryFlysystemTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryFlysystemTransportBundle\Executor;

use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\FilesystemReader;
use League\Flysystem\StorageAttributes;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Vdm\Bundle\LibraryBundle\Model\Message;
use Vdm\Bundle\LibraryBundle\Stamp\StopAfterHandleStamp;

abstract class AbstractFlysystemExecutor implements TransportInterface
{
    public const FILE_EXTRA_TMP_PATH = 'tmpfilepath';

    /**
     * @var FilesystemOperator $storage
     */
    protected $storage;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var SerializerInterface $serializer
     */
    protected $serializer;

    /**
     * AbstractFlysystemExecutor constructor.
     */
    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    /**
     * @return FilesystemOperator
     */
    public function getStorage(): FilesystemOperator
    {
        return $this->storage;
    }

    /**
     * @param FilesystemOperator $storage
     */
    public function setStorage(FilesystemOperator $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param LoggerInterface $logger $logger
     *
     * @return self
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param SerializerInterface $serializer $serializer
     *
     * @return self
     */
    public function setSerializer(SerializerInterface $serializer): self
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * Get file content
     *
     * @param FileAttributes $file
     * @return FileAttributes
     * @throws \League\Flysystem\FilesystemException
     */
    protected function download(FileAttributes $file): FileAttributes
    {
        $tempName = tempnam(sys_get_temp_dir(), 'vdm_' . uniqid());

        $file = $file->jsonSerialize();
        $file[StorageAttributes::ATTRIBUTE_EXTRA_METADATA][self::FILE_EXTRA_TMP_PATH] = $tempName;

        /** @var FileAttributes $file */
        $file = FileAttributes::fromArray($file);

        $this->logger->info(sprintf('Start downloading the file %s to: %s', $file['path'], $tempName));

        $tempFile = fopen($tempName, 'wb+');

        $stream = $this->storage->readStream($file['path']);
        while (!feof($stream)) {
            if (fwrite($tempFile, fread($stream, 8192)) === false) {
                $this->logger->error('Unable to write to temp file');
                break;
            }
        }

        fclose($stream);
        fclose($tempFile);

        $this->logger->info('Download complete');

        return $file;
    }

    /**
     * Get all files/directories in this directory
     *
     * @param string $dirPath directory path to list
     * @param bool $deep
     *
     * @return array list of files or directories in this path
     *
     * @throws \League\Flysystem\FilesystemException
     */
    protected function listContents(string $dirPath, bool $deep = FilesystemReader::LIST_SHALLOW): array
    {
        return $this
            ->storage
            ->listContents($dirPath, $deep)
            ->filter(function (StorageAttributes $attributes) {
                return $attributes->isFile();
            })
            ->toArray();
    }


    /**
     * @param Message $message
     * @param false|bool $isLast
     * @return Envelope
     */
    protected function getEnvelope(Message $message, bool $isLast = false): Envelope
    {
        // Put the stop stamp on the last file
        $stamps = ($isLast) ? [new StopAfterHandleStamp()] : [];

        return new Envelope($message, $stamps);
    }
}
