<?php

namespace LaravelGreatApi\Router;

use Closure;

class PendingResourceRegistration
{
    /**
     * @var ResourceRegistrar
     */
    private ResourceRegistrar $registrar;

    /**
     * @var string
     */
    private string $resource;

    /**
     * @var array
     */
    private array $options = [];

    /**
     * @var bool
     */
    private bool $registered = false;

	/**
	 * Undocumented variable
	 *
	 * @var Closure|null
	 */
	private ?Closure $relationships = null;

	/**
	 * Undocumented function
	 *
	 * @param ResourceRegistrar $registrar
	 * @param string $resource
	 */
	public function __construct(ResourceRegistrar $registrar, string $resource)
	{
		$this->registrar =$registrar;
		$this->resource = $resource;
	}

	public function relationships(Closure $callback)
	{
		$this->relationships = $callback;

		return $this;
	}

	public function register()
	{
		$this->registered = true;

        $routes = $this->registrar->register(
            $this->resource,
            $this->options
        );

		if ($relationships = $this->relationships) {
            $relations = $this->registrar->relationships(
                $this->resource,
                $this->options,
                $relationships
            );

            foreach ($relations as $route) {
                $routes->add($route);
            }
		}

		return $routes;
	}

    /**
     * Handle the object's destruction.
     *
     * @return void
     */
    public function __destruct()
    {
        if (!$this->registered) {
            $this->register();
        }
    }
}
