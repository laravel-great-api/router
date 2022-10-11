<?php

namespace LaravelGreatApi\Router\Registrars;

trait FetchManyResourceRegistrar
{
	/**
	 * Регистратор index ресурса
	 *
	 * @param string $resource
	 * @param string $method
	 * @param array $options
	 * @return void
	 */
	protected function fetchManyResourceRegistrar(string $resource, string $method, array $options)
	{
		return $this->router->get($resource, $this->getResourceAction($resource, $method, $options));
	}
}
