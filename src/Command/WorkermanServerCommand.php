<?php

declare(strict_types=1);

namespace Chiron\Workerman\Command;

use Chiron\Boot\Directories;
use Chiron\Boot\Environment;
use Chiron\Console\AbstractCommand;
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
    public function perform(Environment $environement, Directories $directories): int
    {
        $_SERVER['WORKER_MAN'] = 'true';
        //$_ENV['REACT_PHP'] = 'true';
        //putenv("REACT_PHP=true");

        //$environement->set('REACT_PHP', true);

        include $directories->get('@public/index.php');

        return self::SUCCESS;
    }
}
