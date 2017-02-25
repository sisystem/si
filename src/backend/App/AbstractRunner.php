<?php
/**
 *  SI - Next Generation PHP Framework
 *  Copyright (c) Maciej Helminiak <maciej.helminiak@opmbx.org>
 *  License is distributed with this source code in LICENSE file.
 */

namespace Si\App;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\SapiEmitter;
use Si\App\AbstractApplication;

abstract class AbstractRunner
{
    protected $app = null;

    public function __construct(AbstractApplication $app)
    {
        $this->app = $app;
    }

    abstract public function run(): void;

    protected function sendResponseSapi(ResponseInterface $response): void
    {
        (new SapiEmitter())->emit($response);
    }
}
