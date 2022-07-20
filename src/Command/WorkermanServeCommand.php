<?php

declare(strict_types=1);

namespace Chiron\Workerman\Command;

use Chiron\Core\Directories;
use Chiron\Core\Environment;
use Chiron\Core\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Output\OutputInterface;
use Chiron\Workerman\WorkermanWebServer;
use Chiron\WebServer\WebServerInterface;
use Chiron\WebServer\Exception\WebServerException;

//https://github.com/top-think/think-worker/blob/3.0/src/command/Server.php

/**
 * Runs Chiron application using a local Workerman web server.
 */
final class WorkermanServeCommand extends AbstractCommand
{
    protected static $defaultName = 'workerman:serve';

    protected function configure(): void
    {
        $this
            ->setDescription('Start a standalone local Workerman web server.')
            ->addArgument('address', InputArgument::OPTIONAL, 'Host to serve at', '0.0.0.0')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port to serve at', 8080);
    }

    public function perform(Directories $directories): int
    {
        // TODO : améliorer le code !!!!
        $input = $this->input;
        $output = $this->output;

        $address = $input->getArgument('address');
        $port = $input->getOption('port');

        //$docroot = $input->getOption('docroot');
        //$router = $input->getOption('router');

        //$documentRoot = getcwd() . '/' . $docroot; // TODO: can we do it better?

        if (strpos($address, ':') === false) {
            $address .= ':' . $port;
        }

        $this->success("Server listening on http://{$address}");
        $this->info('Quit the server with CTRL-C or COMMAND-C.');

        [$hostname, $port] = explode(':', $address);

        try {
            $server = new WorkermanWebServer($hostname, (int) $port);
            $server->run($this->isDisabledOutput(), $this->getOutputCallback());
        } catch (WebServerException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function isDisabledOutput(): bool
    {
        return $this->output->isQuiet();
    }

    private function getOutputCallback(): callable
    {
        $output = $this->output;

        return static function (string $type, string $buffer) use ($output) {
            if (Process::ERR === $type && $output instanceof ConsoleOutputInterface) { // TODO : faire en sorte d'éviter cette dépendance vers la classe Process dans la partie "use" de cette classe
                $output = $output->getErrorOutput();
            }
            $output->write($buffer, false, OutputInterface::OUTPUT_RAW);
        };
    }








    // TODO : essayer de faire en sorte que la classe Environment ne soit pas écrasée lorsqu'on initialise l'application ca permettre d'utiliser cette classe plutot que directement la variable $_SERVER !!!!
    //public function perform(Environment $environement, Directories $directories): int
    public function perform_OLD(Directories $directories): int
    {
        $_SERVER['WORKER_MAN'] = 'http://0.0.0.0:8080';

        //$_SERVER['WORKER_MAN'] = 'true';



        //$_ENV['REACT_PHP'] = 'true';
        //putenv("REACT_PHP=true");

        //$environement->set('REACT_PHP', true);

        include $directories->get('@public/index.php');

        return self::SUCCESS;
    }


    public function perform_GOOD(Environment $environement, Directories $directories): int
    {
        $path = $directories->get('@public/index2.php');

        passthru('"' . PHP_BINARY . '"' . " -f \"{$path}\"");

        return self::SUCCESS;
    }

    // TODO : essayer de faire en sorte que la classe Environment ne soit pas écrasée lorsqu'on initialise l'application ca permettre d'utiliser cette classe plutot que directement la variable $_SERVER !!!!
    public function perform_BAD(Environment $environement, Directories $directories): int
    {
        $command = '$_SERVER[\'WORKER_MAN\'] = true; include \'D:/xampp/htdocs/myapp/public/index3.php\';';
        //$command = '$_SERVER[\'WORKER_MAN\'] = \'true\';include \'D:/xampp/htdocs/myapp/public/index.php\';';

        //$command = "$_SERVER['WORKER_MAN'] = 'true';include 'D:/xampp/htdocs/myapp/public/index.php';";
        passthru(sprintf('"%s" -r "%s"', PHP_BINARY, $command));

        //passthru('"' . PHP_BINARY . '"' . " -r \"{$path}\"");

        return self::SUCCESS;
    }


    public function perform_OLD2(Environment $environement, Directories $directories): int
    {
        $_SERVER['WORKER_MAN'] = 'true';

        //$_ENV['WORKER_MAN'] = 'true';
        //putenv("WORKER_MAN=true");

        //$_ENV['REACT_PHP'] = 'true';
        //putenv("REACT_PHP=true");

        //$environement->set('REACT_PHP', true);

        //unset($argv[0]);

        include $directories->get('@public/index.php');

        return self::SUCCESS;
    }
}
