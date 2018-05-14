<?php

namespace Tester {

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

        public function productAction(string $query): string
        {
            return 'Called: ' . __METHOD__ . "\n" . 'Query string: ' . $query;
        }
    }
}

namespace {
    include dirname(__DIR__) . '/vendor/autoload.php';

    use Phroute\Phroute\Dispatcher;
    use Phroute\Phroute\RouteCollector;

    $collector = new RouteCollector();

    $collector->get('', ['\\Tester\\Controller', 'indexAction']);
    $collector->get('listing/{page}', ['\\Tester\\Controller', 'listingAction']);
    $collector->get('product?{query}', ['\\Tester\\Controller', 'productAction']);

    $dispatcher =  new Dispatcher($collector->getData());

    echo $dispatcher->dispatch('GET', '/'), "\n";
    echo $dispatcher->dispatch('GET', 'listing/2'), "\n";
    echo $dispatcher->dispatch('GET', 'product?action=edit&id=coffee'), "\n";
}

