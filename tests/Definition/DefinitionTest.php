<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\Route;
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Definition\FilterDefinitionAbsract;
use Phroute\Phroute\Definition\GroupDefinition;
use Phroute\Phroute\Definition\RouteDefinition;
use Phroute\Phroute\Exception\BadDefinitionException;

class DefinitionTest extends TestCase
{
    public function testDefinitionFileLoad(): void {
        $collector = new RouteCollector();
        $collector->addDefinitions($this->getDefinition());
        $dispatcher =  new Dispatcher($collector->getData());
        $this->assertSame('Called: BaseController::indexAction', $dispatcher->dispatch('GET', '/'));
        $this->assertStringContainsString('Called: Controller::listingAction', $dispatcher->dispatch('GET', 'listing/2'));
        $this->assertStringContainsString('params: action=edit&id=coffee', $dispatcher->dispatch('GET', 'product?action=edit&id=coffee'));
    }

     function testRouteDefinitionClass(): void {
        $collector = new RouteCollector();
        $definitions = new RouteDefinition();
        $definitions->addRoute(Route::GET, '', [Controller::class, 'indexAction']);
        $definitions->addRoute(Route::GET, 'listing/{page}', [Controller::class, 'listingAction']);
        $definitions->addRoute(Route::GET, 'product?{query}', [Controller::class, 'productAction']);

        $collector->addDefinitions($definitions);
        $dispatcher =  new Dispatcher($collector->getData());

        $this->assertSame('Called: BaseController::indexAction', $dispatcher->dispatch('GET', '/'));
        $this->assertStringContainsString('Called: Controller::listingAction', $dispatcher->dispatch('GET', 'listing/2'));
        $this->assertStringContainsString('params: action=edit&id=coffee', $dispatcher->dispatch('GET', 'product?action=edit&id=coffee'));
    }

    function testRouteGroupClass(): void {
        $collector = new RouteCollector();
        $definitions = new GroupDefinition('admin');
        $definitions->addRoute(Route::GET, 'product/{action}', [Controller::class, 'productAction']);

        $collector->addDefinitions($definitions);
        $dispatcher =  new Dispatcher($collector->getData());

        $this->assertStringContainsString('params: edit', $dispatcher->dispatch('GET', 'admin/product/edit'));
    }

    function testFilterClass(): void {
        $collector = new RouteCollector();
        $this->assertNotNull($collector);
        $beforeFilter = new AuthFilterDefinition('auth');
        $collector->addDefinitions($beforeFilter);
        $afterFilter = new AnotherFilter('done');
        $collector->addDefinitions($afterFilter);

        $definitions = new GroupDefinition('admin');
        $definitions->addRoute(Route::GET, 'product/{action}', [Controller::class, 'productAction']);
        $definitions->addBeforeFilter($beforeFilter);
        $definitions->addAfterFilter($afterFilter);

        $collector->addDefinitions($definitions);

        $dispatcher =  new Dispatcher($collector->getData());
        // We assert false here because AnotherFilter::execute(...) returns false
        $this->assertFalse($dispatcher->dispatch('GET', 'admin/product/edit'));
    }

    function testBadDefinitionException() {
        $this->expectException(BadDefinitionException::class);
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
    public function getVarsAsArray(): array
    {
        return ['path', 'test'];
    }

    public function listingAction(string $page): string
    {
        return 'Called: ' . __METHOD__ . "\n" . 'Page: ' . $page;
    }

    public function productAction(string $query): string
    {
        return 'Called: ' . __METHOD__ . ', params: ' . $query;
    }
}

class AuthFilterDefinition extends FilterDefinitionAbsract
{
    public function execute(...$vars)
    {
        echo 'authenticated, ';
        return;
    }
}

class AnotherFilter extends FilterDefinitionAbsract
{
    public function execute(...$vars)
    {
        echo 'Something needs to be done';
        return false;
    }
}
