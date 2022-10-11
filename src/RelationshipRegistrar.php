<?php

namespace LaravelGreatApi\Router;

use Illuminate\Contracts\Routing\Registrar as RegistrarContract;
use Illuminate\Routing\RouteCollection;
use Closure;

class RelationshipRegistrar
{
	use Registrars\FetchManyResourceRegistrar;

	private RegistrarContract $router;

	private $resource;

	private ?array $resources = [];

	public function __construct(RegistrarContract $router, $resource)
	{
		$this->router = $router;
		$this->resource = $resource;
	}

	public function resource(string $resource, array $options = [])
	{
		$this->resources[$resource] = $options;
	}

	public function resourcesExists()
	{
		return is_array($this->resources);
	}

	private function eachResources(Closure $callback)
	{
		foreach($this->resources as $resource => $options) {
			$callback($resource, $options);
		}
	}

	private function eachResourceMethods(Closure $callback)
	{
		$methods = [
			'FetchMany',
			// 'Store',
			// 'Fetch',
			// 'Update',
			// 'Destroy',
			// 'DestroyMany',
		];

		foreach($methods as $method) {
			$this->eachResources(function($resource, $options) use($method, $callback) {
				$callback($resource, $method, $options);
			});
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
     * Get the action array for a resource route.
     *
     * @param string $resource
     * @param string $controller
     * @param string $method
     * @param string|null $parameter
     * @param array $options
     * @return array
     */
    private function getResourceAction(string $resource, string $method, array $options)
	{
        $name = $this->getResourceRouteName($resource, $method, $options);

        $action = ['as' => $name, 'uses' => '\App\Http\Controllers\Api\\' . ucfirst($this->resource) . "\\{$method}Controller" . '@__invoke'];

        if (isset($options['middleware'])) {
            $action['middleware'] = $options['middleware'];
        }

        if (isset($options['excluded_middleware'])) {
            $action['excluded_middleware'] = $options['excluded_middleware'];
        }

        return $action;
    }

    /**
     * @param string $fieldName
     * @param bool $hasMany
     * @param array $options
     * @return RouteCollection
     */
    public function register(): RouteCollection
    {
        $routes = new RouteCollection();

		$this->eachResourceMethods(function($resource, $method, $options) use($routes) {
			$fn = lcfirst($method) . "ResourceRegistrar";

			$route = $this->{$fn}($resource, $method . ucfirst($resource), $options);

			$routes->add($route);
		});

		return $routes;
    }
}
