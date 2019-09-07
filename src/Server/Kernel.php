<?php

namespace VinyVicente\JsonRpc\Server;

use Exception;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;
use VinyVicente\JsonRpc\Contracts\ExceptionHandlerContract;
use VinyVicente\JsonRpc\Contracts\KernelContract;
use VinyVicente\JsonRpc\Routing\Router;
use Illuminate\Support\Facades\Facade;
use Illuminate\Contracts\Foundation\Application;

class Kernel implements KernelContract
{
    /**
     * The application implementation.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The router instance.
     *
     * @var \VinyVicente\JsonRpc\Routing\Router
     */
    protected $router;

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [];

    /**
     * Create a new JSON-RPC kernel instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \VinyVicente\JsonRpc\Routing\Router $router
     */
    public function __construct(Application $app, Router $router)
    {
        $this->app = $app;
        $this->router = $router;

        foreach ($this->routeMiddleware as $key => $middleware) {
            $router->aliasMiddleware($key, $middleware);
        }
    }

    /**
     * Handle an incoming JSON-RPC request.
     *
     * @param  \VinyVicente\JsonRpc\Server\Request $request
     * @return \VinyVicente\JsonRpc\Server\Response
     */
    public function handle($request)
    {
        try {
            $this->app->instance('swoole.jsonrpc.request', $request);

            Facade::clearResolvedInstance('swoole.jsonrpc.request');

            $response = $this->router->dispatch($request);
        } catch (Exception $e) {
            $this->reportException($e);

            $response = $this->renderException($request, $e);
        } catch (Throwable $e) {
            $this->reportException($e = new FatalThrowableError($e));

            $response = $this->renderException($request, $e);
        }

        return $response;
    }

    /**
     * Call the terminate method on any terminable middleware.
     *
     * @param  \VinyVicente\JsonRpc\Server\Request $request
     * @param  \VinyVicente\JsonRpc\Server\Response $response
     * @return void
     */
    public function terminate($request, $response)
    {
        $this->terminateMiddleware($request, $response);

        if (method_exists($this->app, 'terminate')) {
            $this->app->terminate();
        }
    }

    /**
     * Call the terminate method on any terminable middleware.
     *
     * @param \VinyVicente\JsonRpc\Server\Request $request
     * @param \VinyVicente\JsonRpc\Server\Response $response
     * @return void
     */
    protected function terminateMiddleware($request, $response)
    {
        $middlewares = $this->app->shouldSkipMiddleware() ? [] : $this->gatherRouteMiddleware($request);

        foreach ($middlewares as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }

            list($name, $parameters) = $this->parseMiddleware($middleware);

            $instance = $this->app->make($name);

            if (method_exists($instance, 'terminate')) {
                $instance->terminate($request, $response);
            }
        }
    }

    /**
     * Gather the route middleware for the given request.
     *
     * @param  \VinyVicente\JsonRpc\Server\Request $request
     * @return array
     */
    protected function gatherRouteMiddleware($request)
    {
        if ($route = $request->route()) {
            return $this->router->gatherRouteMiddleware($route);
        }

        return [];
    }

    /**
     * Parse a middleware string to get the name and parameters.
     *
     * @param  string $middleware
     * @return array
     */
    protected function parseMiddleware($middleware)
    {
        list($name, $parameters) = array_pad(explode(':', $middleware, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param  \Exception $e
     * @return void
     */
    protected function reportException(Exception $e)
    {
        $this->app[ExceptionHandlerContract::class]->report($e);
    }

    /**
     * Render the exception to a response.
     *
     * @param  \VinyVicente\JsonRpc\Server\Request $request
     * @param  \Exception $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderException($request, Exception $e)
    {
        return $this->app[ExceptionHandlerContract::class]->render($request, $e);
    }

    /**
     * Get the Laravel application instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    public function getApplication()
    {
        return $this->app;
    }
}
