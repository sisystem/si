<?php
/**
 *  SI - Next Generation PHP Framework
 *  Copyright (c) Maciej Helminiak <maciej.helminiak@opmbx.org>
 *  License is distributed with this source code in LICENSE file.
 */

namespace Si\Base\Server;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\SapiEmitter;
use Si\Base\App\ApplicationInterface;

abstract class ServerAbstract
{
    protected $app = null;

    public function __construct(ApplicationInterface $app)
    {
        $this->app = $app;
    }

    abstract public function run(): void;

    protected function sendResponseSapi(ResponseInterface $response): void
    {
        (new SapiEmitter())->emit($response);
    }
}
