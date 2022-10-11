<?php

namespace LaravelGreatApi\Router;

use Closure;
use Illuminate\Contracts\Routing\Registrar as RegistrarContract;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class ResourceRegistrar
{
	use
		Registrars\FetchManyResourceRegistrar,
		Registrars\FetchResourceRegistrar;

	private RegistrarContract $router;

	public function __construct(RegistrarContract $router)
	{
		$this->router = $router;
	}

    /**
     * Start to register resource routes.
     *
     * @param string $resource
     * @param string|null $namespace
     * @return PendingResourceRegistration
     */
    public function resource(string $resource, string $namespace = null): PendingResourceRegistration
    {
        return new PendingResourceRegistration(
            $this,
            $resource,
			$namespace
        );
    }

    public function relationships(string $resource, array $options, Closure $callback): RouteCollection
    {
        $parameter = $this->getResourceParameterName($resource, $options);
        $attributes = $this->getRelationshipsAction($resource, $parameter, $options);

        $registrar = new RelationshipRegistrar(
            $this->router,
            $resource,
            $parameter,
        );

        $routes = new RouteCollection();

        $this->router->group($attributes, function () use ($registrar, $callback, $routes) {
            $callback($registrar, $this->router);

            foreach ($registrar->register() as $route) {
                $routes->add($route);
            }
        });

        return $routes;
    }

	public function register(string $resource, array $options)
	{
		$routes = new RouteCollection();

		$this->eachResourceMethods(function($method) use($resource, $options, $routes) {
			$fn = lcfirst($method) . "ResourceRegistrar";

			$route = $this->{$fn}($resource, $method, $options);
			$routes->add($route);
		});

		return $routes;
	}

	private function eachResourceMethods(Closure $callback)
	{
		$methods = [
			'FetchMany',
			// 'Store',
			'Fetch',
			// 'Update',
			// 'Destroy',
			// 'DestroyMany',
		];

		foreach($methods as $method) {
			$callback($method);
		}
	}

    /**
     * Get the route name.
     *
     * @param string $resource
     * @param string $method
     * @param array $options
     * @return string
     */
    protected function getResourceRouteName(string $resource, string $method, array $options): string
    {
        $custom = $options['names'] ?? [];

        return $custom[$method] ?? "{$resource}." . lcfirst($method);
    }

    /**
     * @param string $resource
     * @param array $options
     * @return string
     */
    public function getResourceParameterName(string $resource, array $options): string
    {
        if (isset($options['parameter'])) {
            return $options['parameter'];
        }

        $param = Str::singular($resource);

        /**
         * Dash-case is not allowed for route parameters. Therefore if the
         * resource type contains a dash, we will underscore it.
         */
        if (Str::contains($param, '-')) {
            $param = Str::underscore($param);
        }

        return $param;
    }

    /**
     * Get the action array for a resource route.
     *
     * @param string $resource
     * @param string $controller
     * @param string $method
     * @param string|null $parameter
     * @param array $options
     * @return array
     */
    private function getResourceAction(
        string $resource,
        string $method,
        array $options
    ) {
        $name = $this->getResourceRouteName($resource, $method, $options);

        $action = ['as' => $name, 'uses' => '\App\Http\Controllers\Api\\' . ucfirst($resource) . "\\{$method}Controller" . '@__invoke'];

        if (isset($options['middleware'])) {
            $action['middleware'] = $options['middleware'];
        }

        if (isset($options['excluded_middleware'])) {
            $action['excluded_middleware'] = $options['excluded_middleware'];
        }

        return $action;
    }

    /**
     * Get the action array for the relationships group.
     *
     * @param string $resource
     * @param string|null $parameter
     * @param array $options
     * @return array
     */
    private function getRelationshipsAction(string $resource, ?string $parameter, array $options)
    {
        $action = [
            'prefix' => sprintf('%s/{%s}', $resource, $parameter),
            'as' => "{$resource}.",
        ];

        if (isset($options['middleware'])) {
            $action['middleware'] = $options['middleware'];
        }

        if (isset($options['excluded_middleware'])) {
            $action['excluded_middleware'] = $options['excluded_middleware'];
        }

        return $action;
    }
}
