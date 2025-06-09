<?php
namespace Core;

class App {
    protected $router;

    public function __construct($router) {
        $this->router = $router;
    }

    public function run() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        $this->router->dispatch($uri, $method);
    }
}
