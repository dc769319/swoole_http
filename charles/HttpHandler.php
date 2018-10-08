<?php

namespace Charles;

use Swoole\Http\Request;
use Swoole\Http\Response;

class HttpHandler
{
    /**
     * @param Request $request
     * @param Response $response
     */
    public function onRequest(Request $request, Response $response)
    {
        if (!isset($request->server['request_uri'])) {
            $response->end('Request error');
        }
        $uri = trim($request->server['request_uri']);
        $response->end(sprintf('Your request uri is "%s"', $uri));
    }
}
