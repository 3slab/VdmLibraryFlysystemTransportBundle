<?php

/**
 * @package    3slab/LibraryFlysystemTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryFlysystemTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryFlysystemTransportBundle\Transport;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Vdm\Bundle\LibraryBundle\Transport\TransportCollectableInterface;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\AbstractFlysystemExecutor;

class FlysystemTransport implements TransportInterface, TransportCollectableInterface
{
    /**
     * @var AbstractFlysystemExecutor $executor
     */
    private $executor;

    /**
     * @var LoggerInterface $logger
    */
    private $logger;

    /**
     * FlysystemTransport constructor.
     *
     * @param AbstractFlysystemExecutor $executor
     * @param LoggerInterface $logger
     */
    public function __construct(
        AbstractFlysystemExecutor $executor,
        LoggerInterface $logger
    ) {
        $this->executor = $executor;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function get(): iterable
    {
        return $this->executor->get();
    }

    /**
     * {@inheritDoc}
     */
    public function ack(Envelope $envelope): void
    {
        $this->executor->ack($envelope);
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     */
    public function reject(Envelope $envelope): void
    {
        $this->executor->reject($envelope);
    }

    /**
     * @param Envelope $envelope
     * @return Envelope
     * @throws \Exception
     */
    public function send(Envelope $envelope): Envelope
    {
        throw new \Exception('Flysystem transport does not support the send action');
    }
}
