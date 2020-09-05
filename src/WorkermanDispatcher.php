<?php

declare(strict_types=1);

namespace Chiron\Workerman;

use Chiron\ErrorHandler\ErrorHandler;
use Chiron\Dispatcher\AbstractDispatcher;
use Chiron\Http\Http;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Server;
use Throwable;

final class WorkermanDispatcher extends AbstractDispatcher
{
    /** @var Http */
    private $http;
    /** @var ErrorHandler */
    private $errorHandler;

    // TODO : virer le paramétre Environment et utiliser directement la fonction globale getenv()
    public function canDispatch(): bool
    {
        //return true;
        //return php_sapi_name() === 'cli' && $this->env->get('REACT_PHP') !== null;
        return PHP_SAPI === 'cli' && env('REACT_PHP') !== null;
    }

    protected function perform(Http $http, ErrorHandler $errorHandler): void
    {
        $this->http = $http;
        $this->errorHandler = $errorHandler;
        $this->createServer();
    }

    // TODO utiliser l'événement ->on('request', $callable) plutot que de passer le callable en paramétre du serveur !!! exemple ci dessous !!!!
    //https://stackoverflow.com/questions/24310817/using-reactphp-for-sockets-in-php-port-stops-listening
    // TODO : gérer les erreurs !!!!! => https://github.com/apisearch-io/symfony-react-server/blob/master/src/Application.php#L234
    // TODO : autre exemple : https://github.com/driftphp/server/blob/c5b2b530e446c52804f074138aa16ba9a252fc89/src/Application.php#L178
    // TODO : exemple avec un affichage de texte dans la console :      https://github.com/NigelGreenway/reactive-slim/blob/master/src/Server.php#L156
    private function createServer()
    {
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

        //https://github.com/apisearch-io/symfony-react-server/blob/master/src/Application.php#L234
        /*
        $http->on('error', function (\Throwable $e) {
            (new ConsoleException($e, '/', 'EXC', 0))->print();
        });*/

        $loop->run();
    }

    //https://github.com/hunzhiwange/framework/blob/master/src/Leevel/Http/Request.php#L151
    /*
    public function isConsole(): bool
    {
        if ($this->server->get('SERVER_SOFTWARE') === 'swoole-http-server') {
            return false;
        }

        return $this->isRealCli();
    }*/
}
