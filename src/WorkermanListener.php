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

// TODO : utiliser la méthode Worker::log() pour logger le détail des requests/response effectuées !!! Voir même directement un safeEcho : https://github.com/walkor/Workerman/blob/master/Worker.php#L2137

final class WorkermanListener
{
    /** @var callable */
    private $callback;
    /** @var WorkermanPsrRequestFactory */
    private $requestFactory;

    public function __construct(WorkermanPsrRequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    public function onRequest(callable $callback): void
    {
        $this->callback = $callback;
    }

    // TODO : renommer la méthode en run() ou loop() ???
    public function listen(): void
    {
        // TODO : lever une exception si le $this->callback n'est pas initialisé !!!!
        $server = $this->prepareServer();
        Worker::runAll();
    }

    private function prepareServer(): Worker
    {
        $host = env('WORKER_MAN_HOST');
        $server = new Worker($host); // TODO : récupérer le $_SERVER['WORKER_MAN'] pour initialiser l'adresse du server !!! Lever une exception si cette donnée n'existe pas ou est vide !!!
        $server->count = 4; // $server->count = shell_exec('nproc') ? shell_exec('nproc') : 32;

        $server->onMessage = function (WorkermanTcpConnection $connection, WorkermanRequest $workermanRequest) {
            $request = $this->requestFactory->toPsrRequest($workermanRequest);

            // TODO : lever une exception si le $this->callback n'est pas initialisé !!!!
            $response = call_user_func($this->callback, $request);

            // TODO : lever une exception si l'objet $response n'est pas une instance de psr7ResponseInterface !!!!

            $emitter = new WorkermanEmitter($connection);
            $emitter->emit($response);
        };

        return $server;
    }
}
