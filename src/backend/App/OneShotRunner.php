<?php
/**
 *  SI - Next Generation PHP Framework
 *  Copyright (c) Maciej Helminiak <maciej.helminiak@opmbx.org>
 *  License is distributed with this source code in LICENSE file.
 */

namespace Si\App;

use \Zend\Diactoros\ServerRequestFactory;
use \Psr\Http\Message\ServerRequestInterface;
use \Zend\Diactoros\Response\HtmlResponse;

class OneShotRunner extends AbstractRunner
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

        $default = function (ServerRequestInterface $request) { // TODO: the same is in Router.php
            return new HtmlResponse("DEFAULT"); // TODO, OR should Application define it?
        };

        $response = ($this->app)($request, $default);
        $this->sendResponseSapi($response);

        unset($response);
        unset($request);
    }
}
