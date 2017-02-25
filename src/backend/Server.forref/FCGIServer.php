<?php
/**
 *  SI - Next Generation PHP Framework
 *  Copyright (c) Maciej Helminiak <maciej.helminiak@opmbx.org>
 *  License is distributed with this source code in LICENSE file.
 */

namespace \Si\FastCGI;

use \Si\FastCGI\Connection;
use \Si\Base\ApplicationInterface;

/**
 *  FastCGI Server.
 */
class FCGIServer
{
    private $socket = null;     /// <resource>
    private $app = null;        /// <ApplicationInterface>

    public function __construct(ApplicationInterface $app)
    {
        $this->app = $app;
        $this->socket = fopen('php://fd/0', 'r');
        if (false === $this->socket) {
            throw new \Exception("Could not open fd 0");
        }
        stream_set_blocking($this->socket, 0);
        $this->startLoop();
    }

    public function __destruct()
    {
        stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
    }

    private function startLoop(): void
    {
        while (true) {
            $conn = stream_socket_accept($this->socket);
            if (false === $conn) {
                continue;
            }
            stream_set_blocking($conn, 0);

            $data = stream_get_contents($conn);
            if (false === $data) {
                stream_socket_shutdown($conn, STREAM_SHUT_RDWR);
                continue;
            }

            $fcgiData = new FCGIData($data);
            $fcgiRequest = new FCGIRequest();
            while ($record = $fcgiData->fetchRecord()) {
                $this->handleRecord($record, $fcgiRequest);
                unset($record);
            }

            $fcgiResponse = ($this->app)($fcgiRequest);

            $data = $this->responseToData($fcgiResponse);

            $res_code = fwrite($conn, $data);
            if (false === $res_code) {
                ;
            }

            unset($fcgiResponse);
            unset($fcgiRequest);
            unset($fcgiData);
            stream_socket_shutdown($conn, STREAM_SHUT_RDWR);
        }
    }

    private function handleRecord(FCGIRecord $record, FCGIRequest $fcgiRequest): void
    {
    }

    private function responseToData(FCGIResponse $fcgiResponse): string
    {
    }
}
