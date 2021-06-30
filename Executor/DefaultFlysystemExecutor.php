<?php

/**
 * @package    3slab/VdmLibraryFlysystemTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryFlysystemTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryFlysystemTransportBundle\Executor;

use League\Flysystem\FilesystemReader;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Vdm\Bundle\LibraryBundle\Model\Message;
use Vdm\Bundle\LibraryBundle\Stamp\StopAfterHandleStamp;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Message\FlysystemMessage;

/**
 * Class DefaultFlysystemExecutor
 *
 * @package Vdm\Bundle\LibraryFlysystemTransportBundle\Executor
 */
class DefaultFlysystemExecutor extends AbstractFlysystemExecutor
{
    /**
     * {@inheritDoc}
     * @throws \League\Flysystem\FilesystemException
     */
    public function get(): iterable
    {
        $files = $this->listContents('/');

        foreach ($files as $key => $file) {
            $file = $this->download($file);

            $isLast = array_key_last($files) === $key;

            $message = new FlysystemMessage($file);
            yield $this->getEnvelope($message, $isLast);
        }
    }

    public function ack(Envelope $envelope): void
    {
        $this->logger->debug('flysystem transport default executor does not do anything on ack action');
    }

    public function reject(Envelope $envelope): void
    {
        $this->logger->debug('flysystem transport default executor does not do anything on reject action');
    }

    public function send(Envelope $envelope): Envelope
    {
        $this->logger->debug('flysystem transport default executor does not do anything on send action');
    }
}
