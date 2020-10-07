<?php

declare(strict_types=1);

namespace Chiron\Workerman;

use Chiron\ErrorHandler\ErrorHandler;
use Chiron\Core\Dispatcher\AbstractDispatcher;
use Chiron\Http\Http;
use Psr\Http\Message\ServerRequestInterface;
use Workerman\Worker;
use Workerman\Connection\TcpConnection as WorkermanTcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;
use Throwable;

//https://github.com/chubbyphp/chubbyphp-workerman-request-handler/

final class WorkermanDispatcher extends AbstractDispatcher
{
    /** @var Http */
    private $http;
    /** @var ErrorHandler */
    private $errorHandler;
    /** @var WorkermanPsrRequestFactory */
    private $requestFactory;

    // TODO : virer le paramétre Environment et utiliser directement la fonction globale getenv()
    public function canDispatch(): bool
    {
        //return true;
        //return php_sapi_name() === 'cli' && $this->env->get('WORKER_MAN') !== null;
        return PHP_SAPI === 'cli' && env('WORKER_MAN') !== null;
    }

    protected function perform(Http $http, ErrorHandler $errorHandler, WorkermanPsrRequestFactory $requestFactory): void
    {
        $this->http = $http;
        $this->errorHandler = $errorHandler;
        $this->requestFactory = $requestFactory;
        $this->createServer();
    }

    private function createServer()
    {
        $server = new Worker('http://0.0.0.0:8080');

        $server->count = 4;

/*
        $server->onWorkerStart = function () {
            echo 'Workerman http server is started.'.PHP_EOL;
        };
*/

        $server->onMessage = function (WorkermanTcpConnection $connection, WorkermanRequest $workermanRequest) {


            $verbose = true;

            $request = $this->requestFactory->toPsrRequest($workermanRequest);

            try {
                $response = $this->http->handle($request);
            } catch (Throwable $e) {
                // TODO : il faudrait plutot utiliser le RegisterErrorHandler::renderException($e) pour générer le body de la réponse !!!!
                $response = $this->errorHandler->renderException($e, $request, $verbose);
            }

            $emitter = new WorkermanEmitter($connection);
            $emitter->emit($response);
        };

        Worker::runAll();









/*


        $loop = Factory::create();

        $server = new Server($loop, function (ServerRequestInterface $request) {
            $verbose = true;

            try {
                $response = $this->http->run($request);
            } catch (Throwable $e) {
                // TODO : il faudrait plutot utiliser le RegisterErrorHandler::renderException($e) pour générer le body de la réponse !!!!
                $response = $this->errorHandler->renderException($e, $request, $verbose);
            }

            return $response;
        });

        //$socket = new \React\Socket\Server(isset($argv[1]) ? $argv[1] : '0.0.0.0:0', $loop);
        $socket = new \React\Socket\Server('127.0.0.1:8080', $loop);
        $server->listen($socket);

        echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;

        $loop->run();

*/

    }
}
