<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\Route;
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Definition\GroupDefinition;
use Phroute\Phroute\Definition\RouteDefinition;
use Phroute\Phroute\Definition\FilterDefinition;

class DefinitionTest extends TestCase
{
    public function testDefinitionFileLoad()
    {
        $collector = new RouteCollector();
        $collector->addDefinitions($this->getDefinition());
        $dispatcher =  new Dispatcher($collector->getData());
        $this->assertSame('Called: BaseController::indexAction', $dispatcher->dispatch('GET', '/'));
        $this->assertContains('Called: Controller::listingAction', $dispatcher->dispatch('GET', 'listing/2'));
        $this->assertContains('Params: action=edit&id=coffee', $dispatcher->dispatch('GET', 'product?action=edit&id=coffee'));
    }

    public function testRouteDefinitionClass()
    {
        $collector = new RouteCollector();
        $definitions = new RouteDefinition();
        $definitions->addRoute(Route::GET, '', [Controller::class, 'indexAction']);
        $definitions->addRoute(Route::GET, 'listing/{page}', [Controller::class, 'listingAction']);
        $definitions->addRoute(Route::GET, 'product?{query}', [Controller::class, 'productAction']);

        $collector->addDefinitions($definitions);
        $dispatcher =  new Dispatcher($collector->getData());

        $this->assertSame('Called: BaseController::indexAction', $dispatcher->dispatch('GET', '/'));
        $this->assertContains('Called: Controller::listingAction', $dispatcher->dispatch('GET', 'listing/2'));
        $this->assertContains('Params: action=edit&id=coffee', $dispatcher->dispatch('GET', 'product?action=edit&id=coffee'));
    }

    public function testRouteGroupClass()
    {
        $collector = new RouteCollector();
        $definitions = new GroupDefinition('admin');
        $definitions->addRoute(Route::GET, 'product/{action}', [Controller::class, 'productAction']);

        $collector->addDefinitions($definitions);
        $dispatcher =  new Dispatcher($collector->getData());

        $this->assertContains('Params: edit', $dispatcher->dispatch('GET', 'admin/product/edit'));
    }

    public function testFilterClass()
    {
        $collector = new RouteCollector();
        $this->assertNotNull($collector);
        $beforeFilter = new FilterDefinition('auth');
        $collector->addDefinitions($beforeFilter);

        $definitions = new GroupDefinition('admin');
        $definitions->addRoute(Route::GET, 'product/{action}', [Controller::class, 'productAction']);
        $definitions->addBeforeFilter($beforeFilter);

        $collector->addDefinitions($definitions);

        $dispatcher =  new Dispatcher($collector->getData());
        $this->assertContains('Params: edit', $dispatcher->dispatch('GET', 'admin/product/edit'));
    }

    /**
     * @expectedException \Phroute\Phroute\Exception\BadDefinitionException
     */
    public function testBadDefinitionException()
    {
        $collector = new RouteCollector();
        $collector->addDefinitions('test');
    }

    private function getDefinition(): array
    {
        return [
            Route::GET => [
                '' => [Controller::class, 'indexAction'],
                'product?{query}' => [Controller::class, 'productAction'],
                'listing/{page}' => [Controller::class, 'listingAction']
            ]
        ];
    }
}

abstract class BaseController
{
    public function indexAction(): string
    {
        return 'Called: ' . __METHOD__;
    }
}

class Controller extends BaseController
{
    public function listingAction(string $page): string
    {
        return 'Called: ' . __METHOD__ . "\n" . 'Page: ' . $page;
    }

    public function productAction(string $params): string
    {
        return 'Called: ' . __METHOD__ . "\n" . 'Params: ' . $params;
    }
}
