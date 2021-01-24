<?php

declare(strict_types=1);

namespace Chiron\Workerman\Command;

use Chiron\Core\Directories;
use Chiron\Core\Environment;
use Chiron\Core\Console\AbstractCommand;
use Chiron\Filesystem\Filesystem;

final class WorkermanServerCommand extends AbstractCommand
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    protected static $defaultName = 'workerman:serve';

    protected function configure(): void
    {
        $this->setDescription('Workerman Server.');
    }

    // TODO : essayer de faire en sorte que la classe Environment ne soit pas écrasée lorsqu'on initialise l'application ca permettre d'utiliser cette classe plutot que directement la variable $_SERVER !!!!
    //public function perform(Environment $environement, Directories $directories): int
    public function perform(Directories $directories): int
    {
        $_SERVER['WORKER_MAN'] = 'true';
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
