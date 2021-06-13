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
    public $onMessage;
    /** @var WorkermanPsrRequestFactory */
    private $requestFactory;

    public function __construct(WorkermanPsrRequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    // TODO : renommer la méthode en run() ou loop() ???
    public function listen(): void
    {
        // TODO : lever une exception si le $this->callback n'est pas initialisé !!!!
        $server = $this->prepareServer();
        //Worker::safeEcho('Server Started !' . PHP_EOL);
        Worker::runAll();
    }

    private function prepareServer(): Worker
    {
        $host = env('WORKER_MAN_HOST'); // TODO : ajouter la classe Environment dans le constructeur pour faire un $this->environment->get('XXX') au lieu de passer par la fonction env() ????

        $server = new Worker($host); // TODO : récupérer le $_SERVER['WORKER_MAN'] pour initialiser l'adresse du server !!! Lever une exception si cette donnée n'existe pas ou est vide !!!
        $server->count = 4; // $server->count = shell_exec('nproc') ? shell_exec('nproc') : 32;

/*
// TODO : ajouter un paramétre dans la command pour spécifier le nombre de process !!!
//https://github.com/mezzio/mezzio-swoole/blob/3.4.x/src/Command/StartCommand.php#L36
        Use --num-workers to control how many worker processes to start. If you
do not provide the option, 4 workers will be started.
*/

        $server->onMessage = function (WorkermanTcpConnection $connection, WorkermanRequest $workermanRequest) {
            $request = $this->requestFactory->toPsrRequest($workermanRequest);
            $response = call_user_func($this->onMessage, $request);

            $emitter = new WorkermanEmitter($connection);
            $emitter->emit($response);
        };

        return $server;
    }

    /**
 * @return int
 */
    //https://github.com/TechEmpower/FrameworkBenchmarks/blob/master/frameworks/PHP/webman/support/helpers.php#L248
    /*
function cpu_count() {
    if (strtolower(PHP_OS) === 'darwin') {
        $count = shell_exec('sysctl -n machdep.cpu.core_count');
    } else {
        $count = shell_exec('nproc');
    }
    $count = (int)$count > 0 ? (int)$count : 4;
    return $count;
}*/

}
