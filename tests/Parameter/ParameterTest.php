<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\Route;
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Definition\RouteDefinition;
use Phroute\Phroute\Parameter\ParameterSetterInterface;

class ParameterTest extends TestCase
{
    function testRequestPassing(): void {
        $definitions = new RouteDefinition();
        $definitions->addRoute(Route::GET, '', [MainController::class, 'indexAction']);

        $collector = new RouteCollector();
        $collector->addDefinitions($definitions);
        $dispatcher =  new Dispatcher($collector->getData());

        $this->assertInstanceOf(Request::class, $dispatcher->dispatch('GET', '/', new Request()));
    }
}

class MainController implements ParameterSetterInterface
{
    private Request $request;

    function setParameters(...$params): void {
        $this->request = $params[0];
    }

    function indexAction(): Request {
        return $this->request;
    }
}

class Request
{

}
