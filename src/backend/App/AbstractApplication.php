<?php
/**
 *  SI - Next Generation PHP Framework
 *  Copyright (c) Maciej Helminiak <maciej.helminiak@opmbx.org>
 *  License is distributed with this source code in LICENSE file.
 */

namespace Si\App;

use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;

/**
 *  Application.
 */
abstract class AbstractApplication
{
    use PipesConductorTrait;

    public function __invoke(ServerRequestInterface $request, $delegate): ResponseInterface
    {
        return $this->dispatchPipes($request, $delegate);
    }
}
