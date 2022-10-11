<?php

namespace LaravelGreatApi\Router\Registrars;

trait FetchResourceRegistrar
{
	/**
	 * Регистратор show ресурса
	 *
	 * @param string $resource
	 * @param string $method
	 * @param array $options
	 * @return void
	 */
	protected function fetchResourceRegistrar(string $resource, string $method, array $options)
	{
		$parameter = $this->getResourceParameterName($resource, $options);

		$uri = sprintf('%s/{%s}', $resource, $parameter);

		$action = $this->getResourceAction($resource, $method, $options);

		return $this->router->get($uri, $action);
	}
}
