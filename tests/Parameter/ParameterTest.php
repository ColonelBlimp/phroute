<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\Route;
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Definition\RouteDefinition;
use Phroute\Phroute\Parameter\ParameterSetterInterface;

class ParameterTest extends TestCase
{
    public function testRequestPassing()
    {
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
    private $request;

    public function setParameters(...$params): void
    {
        $this->request = $params[0][0];
    }

    public function indexAction(): Request
    {

        return $this->request;
    }
}

class Request
{

}
