<?php declare(strict_types=1);

namespace Phroute\Phroute;

interface HandlerResolverInterface {

	/**
	 * Create an instance of the given handler.
	 *
	 * @param mixed $handler
	 * @param mixed $params
	 * @return callable
	 */
	function resolve($handler, ...$params): callable;
}
