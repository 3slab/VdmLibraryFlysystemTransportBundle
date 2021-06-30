<?php

/**
 * @package    3slab/VdmLibraryFlysystemTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryFlysystemTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryFlysystemTransportBundle\Transport;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\FlysystemBundle\Lazy\LazyFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\FlysystemExecutorRegistry;

class FlysystemTransportFactory implements TransportFactoryInterface
{
    private const DSN_PATTERN_MATCHING  = '/(?P<protocol>[^:]+:\/\/)(?P<storage>.*)/';
    private const DSN_PROTOCOL_FLYSYSTEM = 'vdm+flysystem://';

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var LazyFactory
     */
    private $flysystemFactory;

    /**
     * @var FlysystemExecutorRegistry
     */
    private $executorRegistry;

    /**
     * FlysystemTransportFactory constructor.
     *
     * @param LazyFactory $flysystemFactory
     * @param FlysystemExecutorRegistry $executorRegistry
     * @param LoggerInterface $logger
     */
    public function __construct(
        LazyFactory $flysystemFactory, // Warning internal service may change in the future
        FlysystemExecutorRegistry $executorRegistry,
        LoggerInterface $logger
    ) {
        $this->flysystemFactory = $flysystemFactory;
        $this->executorRegistry = $executorRegistry;
        $this->logger = $logger;
    }

    /**
     * @param string $dsn
     * @param array $options
     * @param SerializerInterface $serializer
     * @return TransportInterface
     */
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $storage = $this->getStorage($dsn);

        if (isset($options['flysystem_executor'])) {
            $executor = $this->executorRegistry->get($options['flysystem_executor']);
        } else {
            $executor = $this->executorRegistry->getDefault();
        }

        $this->logger->debug(sprintf('Flysystem executor loaded is an instance of "%s"', get_class($executor)));

        $executor->setStorage($storage);
        $executor->setOptions($options);

        return new FlysystemTransport($executor, $this->logger);
    }

    /**
     * @noinspection PhpStrFunctionsInspection
     * @param string $dsn
     * @param array $options
     * @return bool
     */
    public function supports(string $dsn, array $options): bool
    {
        if (0 === strpos($dsn, self::DSN_PROTOCOL_FLYSYSTEM)) {
            $this->getStorage($dsn);

            return true;
        }

        return false;
    }

    /**
     * Returns the manager from Doctrine registry.
     *
     * @param string $dsn
     * @return Filesystem
     */
    protected function getStorage(string $dsn): Filesystem
    {
        preg_match(static::DSN_PATTERN_MATCHING, $dsn, $match);

        $match['storage'] = $match['storage'] ?: 'default.storage';

        // Raise exception if the storage does not exist
        return $this->flysystemFactory->createStorage($match['storage'], 'unusedvar');
    }
}
