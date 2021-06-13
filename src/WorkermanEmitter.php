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

// TODO : exemple avec du PSR7 : https://github.com/walkor/Workerman/issues/51#issuecomment-684685099
// TODO : au lieu de faire un new WorkermanResponse() il est possible de convertir la réponse en string lors de l'appel à ->send() via le code : https://github.com/walkor/psr7/blob/master/src/functions.php#L42

final class WorkermanEmitter
{
    /** @var WorkermanTcpConnection */
    private $connection;

    public function __construct(WorkermanTcpConnection $connection)
    {
        $this->connection = $connection;
    }

    //TODO : exemple : https://github.com/gotzmann/comet/blob/acf7c66e41232f0ec6f73fc35db5c647c167167d/src/Comet.php#L212
    // TODO : autre exemple avec un keep alive : https://github.com/walkor/webman-framework/blob/master/src/App.php#L372
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
