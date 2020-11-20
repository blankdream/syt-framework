<?php

declare(strict_types=1);

return [
    'http' => [
        'mode' => SWOOLE_PROCESS,
        'server' =>'\think\server\Http',
        'pid_file'=>ROOT_PATH.'/runtime/httpserver.pid',
        'ip' => '127.0.0.1',
        'port' => 9501,
        'callbacks' => [],
        'settings' => [
            'worker_num' => 1,
        ],
        'listen'=>[]
    ],
    'ws' => [
        'mode' => SWOOLE_PROCESS,
        'ip' => '127.0.0.1',
        'port' => 9502,
        'sock_type' => SWOOLE_SOCK_TCP,
        'callbacks' => [
            "open" => [\App\Events\WebSocket::class, 'onOpen'],
            "message" => [\App\Events\WebSocket::class, 'onMessage'],
            "close" => [\App\Events\WebSocket::class, 'onClose'],
        ],
        'settings' => [
            'worker_num' => 1,
            'open_websocket_protocol' => true,
        ],
    ],
];
