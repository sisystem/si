<?php
/**
 *  SI - Next Generation PHP Framework
 *  Copyright (c) Maciej Helminiak <maciej.helminiak@opmbx.org>
 *  License is distributed with this source code in LICENSE file.
 */

namespace Si\App;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Equip\Dispatch\MiddlewarePipe;

abstract class AbstractController
{
    use PipesConductorTrait;

    private $request = null;
    private $delegate = null;

    public function __invoke()
    {
    }

    public function __construct(ServerRequestInterface $request, $delegate)
    {
        $this->request = $request;
        $this->delegate = $delegate;
    }

    public function dispatch(): ResponseInterface
    {
        return $this->dispatchPipes($this->request, $this->delegate);
    }

    public function setGeneral(array $middlewares): void
    {
        $this->pipe = new MiddlewarePipe($middlewares);
    }

    public function setSpecific(array $middlewares): void
    {
        if ( ! $this->pipe) {
            $this->pipe = new MiddlewarePipe($middlewares);
        } else {
            foreach ($middlewares as $middleware) {
                $this->append($middleware);
            }
        }
    }

    public function reroute(string $classname, string $method, array $params = [])
    {
        var_dump($classname);
        $ctrl = new $classname($this->request, $this->delegate);
        $ctrl();
        return call_user_func_array([$ctrl, $method], $params);
    }
}
