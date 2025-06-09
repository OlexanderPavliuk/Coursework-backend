<?php
namespace Core;

class Router
{
    private array  $routes   = [];   // ['GET' => [ '/path' => handler ]]
    private array  $methods  = [];   // map uri ➜ allowed verbs
    private array  $mwTable  = [];   // middleware registry

    /* ───── public API ───── */

    public function get(string $uri, callable|string $action): self
    {
        return $this->add('GET', $uri, $action);
    }
    public function post(string $uri, callable|string $action): self
    {
        return $this->add('POST', $uri, $action);
    }
    public function add(string $verb, string $uri, callable|string $action): self
    {
        [$regex,$keys]             = $this->compile($uri);
        $this->routes[$verb][$regex] = [$action,$keys];
        $this->methods[$uri][]     = $verb;
        return $this;
    }
    public function middleware(string $name, callable $fn = null): void
    {
        $this->mwTable[$name] = $fn;
    }

    /* ───── dispatch ───── */

    public function dispatch(string $requestUri, string $requestMethod): void
    {
        $matchFound = false;

        foreach ($this->routes[$requestMethod] ?? [] as $regex => [$action,$keys]) {
            if (preg_match($regex, $requestUri, $m)) {
                $params      = array_intersect_key($m, array_flip($keys));
                $matchFound  = true;
                $this->run($action, $params);
                return;
            }
        }

        /* URI exists with another method? → 405 */
        foreach ($this->methods as $uri => $verbs) {
            if (preg_match($this->compile($uri)[0], $requestUri)) {
                header('HTTP/1.1 405 Method Not Allowed');
                header('Allow: '.implode(',',$verbs));
                exit('405 – Method Not Allowed');
            }
        }

        /* 404 */
        header('HTTP/1.1 404 Not Found');
        exit('404 – Not Found');
    }

    /* ───── helpers ───── */

    private function compile(string $uri): array
    {
        $keys  = [];
        $regex = preg_replace_callback('#\{([^}/]+)\}#', function ($m) use (&$keys) {
            $keys[] = $m[1];
            return '([^/]+)';
        }, $uri);
        return ['#^'.$regex.'$#', $keys];
    }

    private function run(callable|string $action, array $params): void
    {
        /* simple middleware example */
        if (is_string($action) && str_contains($action,'@')) {
            [$class,$method] = explode('@',$action);
            $controller = new ("App\\Controllers\\$class");
            call_user_func_array([$controller,$method], $params);
        } else {
            call_user_func_array($action,$params);
        }
    }
}
