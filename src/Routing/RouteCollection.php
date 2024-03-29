<?php

namespace VinyVicente\JsonRpc\Routing;

use VinyVicente\JsonRpc\Exceptions\MethodNotFoundException;
use VinyVicente\JsonRpc\Server\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class RouteCollection extends Collection
{
    /**
     * A look-up table of routes by their methods.
     *
     * @var array
     */
    protected $methodList = [];

    /**
     * A look-up table of routes by their names.
     *
     * @var array
     */
    protected $nameList = [];

    /**
     * A look-up table of routes by controller action.
     *
     * @var array
     */
    protected $actionList = [];

    /**
     * Add a Route instance to the collection.
     *
     * @param  \VinyVicente\JsonRpc\Routing\Route $route
     * @return \VinyVicente\JsonRpc\Routing\Route
     */
    public function add(Route $route)
    {
        $this->push($route);
        $this->addLookups($route);

        return $route;
    }

    /**
     * Add the route to any look-up tables if necessary.
     *
     * @param  \VinyVicente\JsonRpc\Routing\Route $route
     * @return void
     */
    protected function addLookups($route)
    {
        $method = $route->getMethod();
        $action = $route->getAction();

        $this->methodList[$method] = $route;

        if (isset($action['as'])) {
            $this->nameList[$action['as']] = $route;
        }

        if (isset($action['controller'])) {
            $this->addToActionList($action, $route);
        }
    }

    /**
     * Add a route to the controller action dictionary.
     *
     * @param  array $action
     * @param  \VinyVicente\JsonRpc\Routing\Route $route
     * @return void
     */
    protected function addToActionList($action, $route)
    {
        $this->actionList[trim($action['controller'], '\\')] = $route;
    }

    /**
     * Find the first route matching a given request.
     *
     * @param  \VinyVicente\JsonRpc\Server\Request $request
     * @return \VinyVicente\JsonRpc\Routing\Route
     *
     * @throws \VinyVicente\JsonRpc\Exceptions\MethodNotFoundException
     */
    public function match(Request $request)
    {
        $route = Arr::first($this->all(), function ($route) use ($request) {
            return $route->match($request);
        });

        if (is_null($route)) {
            throw new MethodNotFoundException;
        }

        return $route;
    }

    /**
     * Determine if the route collection contains a given named route.
     *
     * @param  string $name
     * @return bool
     */
    public function hasNamedRoute($name)
    {
        return ! is_null($this->getByName($name));
    }

    /**
     * Get a route instance by its name.
     *
     * @param  string $name
     * @return \Illuminate\Routing\Route|null
     */
    public function getByName($name)
    {
        return isset($this->nameList[$name]) ? $this->nameList[$name] : null;
    }

    /**
     * Get a route instance by its controller action.
     *
     * @param  string $action
     * @return \Illuminate\Routing\Route|null
     */
    public function getByAction($action)
    {
        return isset($this->actionList[$action]) ? $this->actionList[$action] : null;
    }

    /**
     * Get all of the routes in the collection.
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->all();
    }
}
