<?php

declare(strict_types=1);

namespace Chiron\React\Command;

use Chiron\Boot\Directories;
use Chiron\Boot\Environment;
use Chiron\Console\AbstractCommand;
use Chiron\Filesystem\Filesystem;

final class ReactServerCommand extends AbstractCommand
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    protected static $defaultName = 'react:serve';

    protected function configure(): void
    {
        $this->setDescription('React Server.');
    }

    // TODO : essayer de faire en sorte que la classe Environment ne soit pas écrasée lorsqu'on initialise l'application ca permettre d'utiliser cette classe plutot que directement la variable $_SERVER !!!!
    public function perform(Environment $environement, Directories $directories): int
    {
        $_SERVER['REACT_PHP'] = 'true';
        //$_ENV['REACT_PHP'] = 'true';
        //putenv("REACT_PHP=true");

        //$environement->set('REACT_PHP', true);

        include $directories->get('@public/index.php');

        return self::SUCCESS;
    }
}
