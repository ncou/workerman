<?php

declare(strict_types=1);

namespace Chiron\Workerman\Bootloader;

use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Console\Console;
use Chiron\Workerman\Command\WorkermanServeCommand;

final class WorkermanCommandBootloader extends AbstractBootloader
{
    public function boot(Console $console): void
    {
        $console->addCommand(WorkermanServeCommand::getDefaultName(), WorkermanServeCommand::class);
    }
}
