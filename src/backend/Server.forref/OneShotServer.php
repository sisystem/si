<?php
/**
 *  SI - Next Generation PHP Framework
 *  Copyright (c) Maciej Helminiak <maciej.helminiak@opmbx.org>
 *  License is distributed with this source code in LICENSE file.
 */

namespace Si\Base\Server;

use \Zend\Diactoros\ServerRequestFactory;

class OneShotServer extends ServerAbstract
{
    public function run(): void
    {
        $request = ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );

        $response = ($this->app)($request, null);
        $this->sendResponseSapi($response);

        unset($response);
        unset($request);
    }
}
