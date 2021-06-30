<?php

namespace Vdm\Bundle\LibraryFlysystemTransportBundle\Tests\Fixtures\AppBundle\Executor;

use League\Flysystem\FileAttributes;
use League\Flysystem\StorageAttributes;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\DefaultFlysystemExecutor;

class CustomFlysystemExecutor extends DefaultFlysystemExecutor
{
    protected function download(FileAttributes $file): FileAttributes
    {
        $file = parent::download($file);

        $file = $file->jsonSerialize();
        $file[StorageAttributes::ATTRIBUTE_EXTRA_METADATA]['executor'] = 'custom';
        /** @var FileAttributes $file */
        $file = FileAttributes::fromArray($file);

        return $file;
    }
}
