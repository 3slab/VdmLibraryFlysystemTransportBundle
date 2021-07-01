# VdmLibraryFlysystemTransportBundle

[![Build Status](https://travis-ci.com/3slab/VdmLibraryFlysystemTransportBundle.svg?branch=3.x-dev)](https://travis-ci.com/3slab/VdmLibraryFlysystemTransportBundle)

This symfony messenger extension provides a transport to pull data from [flysystem compatible source](https://github.com/thephpleague/flysystem-bundle).

*Note : it uses flysystem 2.x*

## Installation

```bash
composer require 3slab/vdm-library-flysystem-transport-bundle
```

## Configuration reference

```
framework:
    messenger:
        transports:
            consumer:
                dsn: "vdm+flysystem://default.storage"
                retry_strategy:
                    max_retries: 0
                options:
                    flysystem_executor: ~ 
```

Configuration | Description
--- | ---
dsn | the name of the flysystem service you want to collect from prefixed by `vdm+flysystem://`
retry_strategy.max_retries | needs to be 0 because flysystem transport does not support this feature
options.flysystem_executor | set the id (in the container of services) of a custom flysystem executor to use instead of the [DefaultFlysystemExecutor](./Executor/DefaultFlysystemExecutor.php)

## Flysystem Executor

Flysystem executor allows you to customize the behavior of the flysystem transport per transport definition inside your `messenger.yaml` file.
Some example use cases are that you may want to list content recursively or not. Or you want to delete processed files from source on `ack`.

If you don't set a custom `flysystem_executor` option when declaring the transport, the default [DefaultFlysystemExecutor](./Executor/DefaultFlysystemExecutor.php) is used
which makes a shallow list of all files in the flysystem storage and download each file one by one before yielding the file metadata to the transport handler in a 
[FlysystemMessage](./Message/FlysystemMessage.php) instance.

You can override this behavior in your project by providing a class that extends 
`Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\AbstractFlysystemExecutor` or 
`Vdm\Bundle\LibraryFlysystemTransportBundle\Executor\DefaultFlysystemExecutor` 
if you just want to tweak default behavior.

The next example is based on the default executor but perform a deep list in the storage instead of the default shallow one.

```
namespace App\Executor;

namespace Vdm\Bundle\LibraryFlysystemTransportBundle\Executor;

use League\Flysystem\FilesystemReader;
use Symfony\Component\Messenger\Envelope;
use Vdm\Bundle\LibraryFlysystemTransportBundle\Message\FlysystemMessage;

class CustomFlysystemExecutor extends DefaultFlysystemExecutor
{
    /**
     * {@inheritDoc}
     * @throws \League\Flysystem\FilesystemException
     */
    public function get(): iterable
    {
        $files = $this->listContents('/', FilesystemReader::LIST_DEEP);

        usort($files, function ($a, $b) {
            return ($a->path() < $b->path()) ? -1 : 1;
        });

        foreach ($files as $key => $file) {
            $file = $this->download($file);

            $isLast = array_key_last($files) === $key;

            $message = new FlysystemMessage($file);
            yield $this->getEnvelope($message, $isLast);
        }
    }
}

```

There are 2 important things your custom executor needs to do :

* `yield` a new envelope
*  Add a `StopAfterHandleStamp` stamp to the yielded envelope if you want to stop after handling the last message
   (if not, the messenger worker may loop over and will execute it once again without stopping). 
   This is done automatically by the `getEnvelope` method with its param `$isLast`.

*Note : thanks to the yield system, you can implement a loop in your execute function and return items once at a time*

*Note : you can keep state in your custom executor*

Then references this custom executor in your transport definition in your project `messenger.yaml` :

```
framework:
    messenger:
        transports:
            ftp-storage-source:
                options:
                    flysystem_executor: App\Executor\CustomFlysystemExecutor
```
