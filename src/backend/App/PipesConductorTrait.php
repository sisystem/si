<?php
/**
 *  SI - Next Generation PHP Framework
 *  Copyright (c) Maciej Helminiak <maciej.helminiak@opmbx.org>
 *  License is distributed with this source code in LICENSE file.
 */

namespace Si\App;

use \Interop\Http\ServerMiddleware\MiddlewareInterface;
use \Equip\Dispatch\MiddlewarePipe;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;

/**
 *  Manage application flow pipes.
 */
trait PipesConductorTrait
{
    private $pipe = null;
    private $pipeAfter = null;

    public function __destruct()
    {
        echo "[destructing PipesConductorTrait]";
        unset($this->pipeAfter);      // CONSIDER: extending MiddlewarePipe with __destruct to unset middleware inside
        unset($this->pipe);      // CONSIDER: extending MiddlewarePipe with __destruct to unset middleware inside
    }

    public function dispatchPipes(ServerRequestInterface $request, $default): ResponseInterface
    {
        $response = null;

        if ($this->pipe) {
            $response = $this->pipe->dispatch($request, $default);
        }

        if ($this->pipeAfter) {
            $request = $request->withAttribute('Response', $response);
            $response = $this->pipeAfter->dispatch($request, $default);
        }

        return $response;
    }

    public function set(array $middleware): void
    {
        $this->pipe = new MiddlewarePipe($middleware);
    }

    public function append(MiddlewareInterface $middleware): void
    {
        $this->pipe->append($middleware);
    }

    public function setAfter(array $middleware): void
    {
        $this->pipeAfter = new MiddlewarePipe($middleware);
    }

    public function appendAfter(MiddlewareInterface $middleware): void
    {
        $this->pipeAfter->append($middleware);
    }
}
