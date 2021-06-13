<?php

declare(strict_types=1);

namespace Chiron\Workerman;

use Chiron\Http\ErrorHandler\HttpErrorHandler;
use Chiron\Core\Engine\AbstractEngine;
use Chiron\Http\Http;
use Psr\Http\Message\ServerRequestInterface;
use Workerman\Worker;
use Workerman\Connection\TcpConnection as WorkermanTcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;
use Throwable;

//https://github.com/chubbyphp/chubbyphp-workerman-request-handler/

final class WorkermanEngine extends AbstractEngine
{
    public function isActive(): bool
    {
        return PHP_SAPI === 'cli' && env('WORKER_MAN') !== null;
    }

    // TODO : créer une classe HttpHandler qui se serait un HandlerInterface, c'est à dire que ca ferait le code qui est dans la closure onRequest(), donc on pourrait utiliser ce bout de code dans les reactphpDispatcher, RoadRunnerDispatcher et ici. ca permettrait directement de passe à la méthode onRequest([$httpHandler, 'handle']), et donc la gestion du http->handle plus le try/catch et la gestion des exception serait faire dans cette classe HttpHandler::class !!!!!
    protected function perform(WorkermanListener $workerman, Http $http): void
    {
        // Callable used when a new request event is received.
        $workerman->onMessage = [$http, 'handle'];
        // Listen (loop wainting a request) and Emit the response.
        $workerman->listen();
    }
}
