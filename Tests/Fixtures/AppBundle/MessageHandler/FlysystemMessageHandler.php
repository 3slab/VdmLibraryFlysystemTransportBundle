<?php

namespace Vdm\Bundle\LibraryFlysystemTransportBundle\Tests\Fixtures\AppBundle\MessageHandler;

use League\Flysystem\FileAttributes;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Message\FlysystemMessage;

/**
 * Class FlysystemMessageHandler
 * @package Vdm\Bundle\LibraryFlysystemTransportBundle\Tests\Fixtures\AppBundle\MessageHandler
 */
class FlysystemMessageHandler implements MessageSubscriberInterface
{
    /**
     * @var FileAttributes[]
     */
    public $files = [];

    /**
     * @param FlysystemMessage $message
     */
    public function __invoke(FlysystemMessage $message)
    {
        $this->files[] = $message->getPayload();
    }

    /**
     * @return iterable
     */
    public static function getHandledMessages(): iterable
    {
        yield FlysystemMessage::class;
    }
}
