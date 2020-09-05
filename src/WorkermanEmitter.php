<?php

declare(strict_types=1);

namespace Chiron\Workerman;

use Chiron\ErrorHandler\ErrorHandler;
use Chiron\Dispatcher\AbstractDispatcher;
use Chiron\Http\Http;
use Psr\Http\Message\ServerRequestInterface;
use Workerman\Worker;
use Workerman\Connection\TcpConnection as WorkermanTcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;
use Workerman\Protocols\Http\Response as WorkermanResponse;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class WorkermanEmitter
{
    /** @var WorkermanTcpConnection */
    private $connection;

    public function __construct(WorkermanTcpConnection $connection)
    {
        $this->connection = $connection;
    }

    public function emit(ResponseInterface $response): void
    {
        $this->connection->send(
            (new WorkermanResponse())
                ->withStatus($response->getStatusCode(), $response->getReasonPhrase())
                ->withHeaders($response->getHeaders())
                ->withBody((string) $response->getBody())
        );
    }
}
