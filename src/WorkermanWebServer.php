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
use Chiron\WebServer\AbstractWebServer;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Wrapper around the Worerman web server.
 */
final class WorkermanWebServer extends AbstractWebServer
{
    protected $hostname;
    protected $port;
    protected $env = [];

    // TODO : lui passer en paramétre le nombre de worker à créer (4 par défault) et stocker cette valeur dans une variable $_ENV['MORKER_MAN_COUNT']
    public function __construct(string $hostname, int $port)
    {
        $this->hostname = $hostname;
        $this->port = $port;

        $this->env['WORKER_MAN'] = 'true';
        $this->env['WORKER_MAN_HOST'] = sprintf('http://%s:%s', $hostname, $port);
    }

    protected function createServerProcess(): Process
    {
        // Locate the PHP Binary path.
        $finder = new PhpExecutableFinder();
        if (false === $binary = $finder->find(false)) {
            throw new WebServerException('Unable to find the PHP binary.');
        }
        // Prepare the PHP built-in web server command line.
        $process = new Process(
            array_filter(array_merge(
            [$binary],
            $finder->findArguments(),
            [
                '-f',
                directory('@public/index.php'), // TODO : passer en paramétre du constructeur cette information !!!!
            ]
        )));
        // Set current php directory & disable timeout.
        //$process->setWorkingDirectory($this->documentRoot); // TODO : vérifier si on a besoin de cette instruction !!!
        $process->setTimeout(null);
        $process->setEnv($this->env);

        return $process;
    }
}
