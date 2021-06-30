<?php

/**
 * @package    3slab/VdmLibraryFlysystemTransportBundle
 * @copyright  2020 Suez Smart Solutions 3S.lab
 * @license    https://github.com/3slab/VdmLibraryFlysystemTransportBundle/blob/master/LICENSE
 */

namespace Vdm\Bundle\LibraryFlysystemTransportBundle\Executor;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class FlysystemExecutorRegistry
 * @package Vdm\Bundle\LibraryFlysystemTransportBundle\Executor
 */
class FlysystemExecutorRegistry
{
    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var AbstractFlysystemExecutor[] $executors
     */
    private $executors;

    /**
     * @var AbstractFlysystemExecutor|null
     */
    private $defaultExecutor;

    /**
     * FlysystemExecutorRegistry constructor.
     * @param LoggerInterface|null $vdmLogger
     */
    public function __construct(LoggerInterface $vdmLogger = null)
    {
        $this->executors = [];
        $this->logger = $vdmLogger ?? new NullLogger();
    }

    /**
     * @param AbstractFlysystemExecutor $executor
     * @param string $id
     */
    public function addExecutor(AbstractFlysystemExecutor $executor, string $id): void
    {
        $this->executors[$id] = $executor;
        if (get_class($executor) === DefaultFlysystemExecutor::class) {
            $this->defaultExecutor = $executor;
        }
    }

    /**
     * @param string $id
     * @return AbstractFlysystemExecutor
     */
    public function get(string $id): AbstractFlysystemExecutor
    {
        if (!array_key_exists($id, $this->executors)) {
            throw new \RuntimeException(sprintf('No executor found with id "%s"', $id));
        }

        return $this->executors[$id];
    }

    /**
     * @return AbstractFlysystemExecutor
     */
    public function getDefault(): AbstractFlysystemExecutor
    {
        if (!$this->defaultExecutor) {
            throw new \RuntimeException('No executor instance of DefaultFlysystemExecutor found');
        }

        return $this->defaultExecutor;
    }
}
