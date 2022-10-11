<?php

namespace LaravelGreatApi\Router;

use Illuminate\Contracts\Routing\Registrar as RegistrarContract;

class Registrar
{
	/**
	 * Инстанс роутера
	 *
	 * @var RegistrarContract
	 */
	private RegistrarContract $router;

	/**
	 * Атрибуты
	 *
	 * @var array
	 */
    private array $attributes = [];

	/**
	 * Конструктор класса
	 *
	 * @param RegistrarContract $router
	 */
	public function __construct(RegistrarContract $router)
	{
		$this->router = $router;
	}

    /**
     * Register server resources.
     *
     * @param \Closure $callback
     * @return void
     */
    public function resources(\Closure $callback): void
    {
        $this->router->group($this->attributes, function () use ($callback) {
            $callback(new ResourceRegistrar($this->router), $this->router);
        });
    }
}
