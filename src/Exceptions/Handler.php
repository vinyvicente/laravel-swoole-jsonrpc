<?php

namespace VinyVicente\JsonRpc\Exceptions;

use Exception;
use VinyVicente\JsonRpc\Server\Request;
use VinyVicente\JsonRpc\Server\Response;
use Psr\Log\LoggerInterface;
use Illuminate\Contracts\Container\Container;
use VinyVicente\JsonRpc\Contracts\ExceptionHandlerContract as JsonRpcExceptionHandlerContract;
use Symfony\Component\Console\Application as ConsoleApplication;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;

class Handler implements ExceptionHandlerContract, JsonRpcExceptionHandlerContract
{
    /**
     * The container implementation.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * Create a new exception handler instance.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Report or log an exception.
     *
     * @param  \Exception $e
     * @return void
     *
     * @throws \Exception
     */
    public function report(Exception $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        try {
            $logger = $this->container->make(LoggerInterface::class);
        } catch (Exception $ex) {
            throw $e;
        }

        $logger->error($e);
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param  \Exception $e
     * @return bool
     */
    public function shouldReport(Exception $e)
    {
        return ! $this->shouldntReport($e);
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param  \Exception $e
     * @return bool
     */
    protected function shouldntReport(Exception $e)
    {
        $dontReport = array_merge($this->dontReport, [ResponseException::class]);

        return ! is_null(collect($dontReport)->first(function ($type) use ($e) {
            return $e instanceof $type;
        }));
    }

    /**
     * Render an exception into a response.
     *
     * @param  \VinyVicente\JsonRpc\Server\Request $request
     * @param  \Exception $e
     * @return \VinyVicente\JsonRpc\Server\Response
     */
    public function render($request, Exception $e)
    {
        $code = $e->getCode();
        $message = $e->getMessage();
        $data = null;

        if ($e instanceof ResponseException) {
            $data = $e->getData();
        }

        $response = new Response;

        $response->setError($code, $message, $data);

        if ($request instanceof Request) {
            $response->prepare($request);
        }

        return $response;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Exception $e
     */
    public function renderForConsole($output, Exception $e)
    {
        (new ConsoleApplication)->renderException($e, $output);
    }
}
