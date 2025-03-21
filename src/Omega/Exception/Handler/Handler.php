<?php

namespace Omega\Exception\Handler;

use Omega\Exception\Inspector\InspectorInterface;
use Omega\Exception\RunInterface;

/**
 * Abstract implementation of a Handler.
 *
 * @property-read int DONE
 * @property-read int LAST_HANDLER
 * @property-read int QUIT
 */
abstract class Handler implements HandlerInterface
{
    /*
     Return constants that can be returned from Handler::handle
     to message the handler walker.
     */
    public const int DONE = 0x10;
    /**
     * The Handler has handled the Throwable in some way, and wishes to skip any other Handler.
     * Execution will continue.
     */
    public const int LAST_HANDLER = 0x20;
    /**
     * The Handler has handled the Throwable in some way, and wishes to quit/stop execution
     */
    public const int QUIT = 0x30;

    /**
     * @var RunInterface
     */
    private $run;

    /**
     * @var InspectorInterface $inspector
     */
    private $inspector;

    /**
     * @var \Throwable $exception
     */
    private $exception;

    /**
     * @param RunInterface $run
     */
    public function setRun(RunInterface $run)
    {
        $this->run = $run;
    }

    /**
     * @return RunInterface
     */
    protected function getRun()
    {
        return $this->run;
    }

    /**
     * @param InspectorInterface $inspector
     */
    public function setInspector(InspectorInterface $inspector)
    {
        $this->inspector = $inspector;
    }

    /**
     * @return InspectorInterface
     */
    protected function getInspector()
    {
        return $this->inspector;
    }

    /**
     * @param \Throwable $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return \Throwable
     */
    protected function getException()
    {
        return $this->exception;
    }
}
